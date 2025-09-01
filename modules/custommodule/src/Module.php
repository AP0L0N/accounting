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

        // Register Twig extension
        Craft::$app->view->registerTwigExtension(new TwigExtension());
    }
}