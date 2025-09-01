<?php

namespace modules\custommodule\twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\ExtensionInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension implements ExtensionInterface
{
    public function getFilters()
    {
        return [
            new TwigFilter('file_exists', [$this, 'fileExists']),
            new TwigFilter('preg_match_all', [$this, 'pregMatchAll'])
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('get_translations', [$this, 'getTranslations'])
        ];
    }

    /**
     * Return true if local file exists.
     */
    public function fileExists($assetOrPath)
    {
        if(is_null($assetOrPath) || empty($assetOrPath) || !is_string($assetOrPath)) {
            return false;
        }

        return file_exists(join('/', [$_SERVER['DOCUMENT_ROOT'], $assetOrPath]));
    }

    public function pregMatchAll($subject, $pattern)
    {
        $matches = [];

        if (preg_match('/^(.).*\1[imsxADU]*$/', $pattern)) {
            try {
                preg_match_all($pattern, $subject, $matches);
            } catch (\Exception $e) {
                \Craft::error("Invalid regex pattern: $pattern - " . $e->getMessage(), __METHOD__);
            }
        }

        return $matches;
    }

    public function getTranslations($language)
    {
        $translationsPath = getenv('TRANSLATIONS_ROOT') . '/' . $language . '/site.php';
        
        if (file_exists($translationsPath)) {
            return include $translationsPath;
        }
        
        return [];
    }

    public function replace($subject, $search, $replace)
    {
        return str_replace($search, $replace, $subject);
    }
}
