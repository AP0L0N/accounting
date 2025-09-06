<?php
namespace modules\custommodule;

use Craft;
use craft\console\Application as ConsoleApplication;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\web\UrlManager;
use yii\web\Response;
use yii\base\Event;
use yii\base\Module as BaseModule;
use craft\web\twig\variables\Cp;
use craft\elements\Entry;

use modules\custommodule\twig\TwigExtension;

class Module extends BaseModule
{
    public function init()
    {
        parent::init();

        // Set module alias
        Craft::setAlias('@modules/custommodule', $this->getBasePath());

        // Set controller namespace for console vs. web
        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'modules\custommodule\console\controllers';
        } else {
            $this->controllerNamespace = 'modules\custommodule\controllers';
        }

        // Register CP URL rules
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                
            }
        );

        // Register CP nav item
        Event::on(
            Cp::class,
            Cp::EVENT_REGISTER_CP_NAV_ITEMS,
            function (RegisterCpNavItemsEvent $event) {
                
            }
        );

        Event::on(
            Entry::class,
            Entry::EVENT_BEFORE_SAVE,
            function(\craft\events\ModelEvent $event) {
                /** @var Entry $entry */
                $entry = $event->sender;

                // Check if this is an entry in the "invoices" section
                if ($entry->getSection()->handle === 'invoices') {

                    // If proFormaInvoice is true, set invoiceNumberFull to "predračun" and do not set invoiceNumber
                    if ($entry->proFormaInvoice) {
                        $entry->setFieldValue('invoiceNumberFull', 'predračun');
                        $entry->setFieldValue('invoiceNumber', null);
                        
                    } else {
                        // 1. Get and set invoice number
                        $currentYear = date('Y');
                        $dateOfService = $entry->dateOfService;
                        $serviceYear = $dateOfService ? $dateOfService->format('Y') : $currentYear;

                        // Find last invoice in same year (excluding current entry and proFormaInvoice entries)
                        $lastInvoice = Entry::find()
                            ->section('invoices')
                            ->status('live')
                            ->id(['not', $entry->id ?: 0])
                            ->dateOfService(['and', '>= ' . $serviceYear . '-01-01', '< ' . ($serviceYear + 1) . '-01-01'])
                            ->proFormaInvoice(false)
                            ->orderBy('invoiceNumber DESC')
                            ->one();

                        $nextInvoiceNumber = $lastInvoice ? ($lastInvoice->invoiceNumber + 1) : 1;
                        $entry->setFieldValue('invoiceNumber', $nextInvoiceNumber);

                        // 2. Set invoiceNumberFull
                        $entry->setFieldValue('invoiceNumberFull', $nextInvoiceNumber . ' / ' . $serviceYear);
                    }

                    // 3. Set currency date by adding currencyDays to dateOfService
                    $dateOfService = $entry->dateOfService;
                    if ($dateOfService && $entry->currencyDays) {
                        $currencyDate = clone $dateOfService;
                        $currencyDate->add(new \DateInterval('P' . $entry->currencyDays . 'D'));
                        $entry->setFieldValue('currency', $currencyDate);
                    }

                    // 4. Process invoice items and calculate prices
                    $invoiceItems = $entry->invoiceItems ?? [];
                    $totalWithoutVAT = 0;
                    $totalVAT = 0;

                    foreach ($invoiceItems as &$item) {
                        $quantity = (float)($item['quantity'] ?? 0);
                        $pricePerUnit = (float)($item['pricePerUnit'] ?? 0);
                        $vatPercentage = (float)($item['vat'] ?? 0);

                        // Calculate price without VAT
                        $priceWithoutVATForItem = $quantity * $pricePerUnit;

                        // Calculate VAT amount
                        $vatAmount = $priceWithoutVATForItem * ($vatPercentage / 100);

                        // Calculate price with VAT
                        $priceWithVAT = $priceWithoutVATForItem + $vatAmount;

                        // Set priceWithVAT for this item
                        $item['col2'] = (int) $vatPercentage;
                        $item['col3'] = (int) $pricePerUnit;
                        $item['col5'] = (int) $quantity;
                        $item['col6'] = $priceWithVAT;

                        // Add to totals
                        $totalWithoutVAT += $priceWithoutVATForItem;
                        $totalVAT += $vatAmount;
                    }

                    // Update the invoiceItems with calculated prices
                    $entry->setFieldValue('invoiceItems', $invoiceItems);

                    // 5. Set priceWithoutVAT
                    $entry->setFieldValue('priceWithoutVAT', $totalWithoutVAT);

                    // 6. Set VATPrice
                    $entry->setFieldValue('VATPrice', $totalVAT);

                    // 7. Set total
                    $entry->setFieldValue('total', $totalWithoutVAT + $totalVAT);

                    // 8. Set title
                    $customer = $entry->customer->one();
                    $customerTitle = $customer ? $customer->title : 'Unknown Customer';
                    $firstItemTitle = !empty($invoiceItems) ? ($invoiceItems[0]['itemTitle'] ?? 'No items') : 'No items';

                    $entry->title = $entry->invoiceNumberFull . ' - ' . $customerTitle . ' - ' . $firstItemTitle;
                }
            }
        );

        // Register Twig extension
        Craft::$app->view->registerTwigExtension(new TwigExtension());
    }
}