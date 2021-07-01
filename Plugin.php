<?php namespace Lovata\Shopaholic;

use Event;
use Backend;
use System\Classes\PluginBase;

//Console command
use Lovata\Shopaholic\Classes\Console\CheckTableIntegrity;
use Lovata\Shopaholic\Classes\Console\ImportFromXML;
use Lovata\Shopaholic\Classes\Console\PreconfigureImportSettingsFromXML;

//Event list
use Lovata\Shopaholic\Classes\Event\ExtendMenuHandler;
//Brand events
use Lovata\Shopaholic\Classes\Event\Brand\BrandModelHandler;
//Category events
use Lovata\Shopaholic\Classes\Event\Category\CategoryModelHandler;
//Currency events
use Lovata\Shopaholic\Classes\Event\Currency\CurrencyModelHandler;
//Measure events
use Lovata\Shopaholic\Classes\Event\Measure\MeasureModelHandler;
//Offer events
use Lovata\Shopaholic\Classes\Event\Offer\OfferModelHandler;
use Lovata\Shopaholic\Classes\Event\Offer\ExtendOfferFieldsHandler;
//Price events
use Lovata\Shopaholic\Classes\Event\Price\PriceModelHandler;
//Product events
use Lovata\Shopaholic\Classes\Event\Product\ProductModelHandler;
use Lovata\Shopaholic\Classes\Event\Product\ProductRelationHandler;
//Promo block events
use Lovata\Shopaholic\Classes\Event\PromoBlock\PromoBlockModelHandler;
use Lovata\Shopaholic\Classes\Event\PromoBlock\PromoBlockRelationHandler;
//Tax events
use Lovata\Shopaholic\Classes\Event\Tax\TaxModelHandler;
use Lovata\Shopaholic\Classes\Event\Tax\TaxRelationHandler;
use Lovata\Shopaholic\Classes\Event\Tax\ExtendTaxFieldsHandler;

/**
 * Class Plugin
 * @package Lovata\Shopaholic
 * @author  Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class Plugin extends PluginBase
{
    /** @var array Plugin dependencies */
    public $require = ['Lovata.Toolbox'];

    /**
     * Register artisan command
     */
    public function register()
    {
        $this->registerConsoleCommand('shopaholic:check.table.integrity', CheckTableIntegrity::class);
        $this->registerConsoleCommand('shopaholic:import_from_xml', ImportFromXML::class);
        $this->registerConsoleCommand('shopaholic:preconfigure_import_from_xml', PreconfigureImportSettingsFromXML::class);
    }

    /**
     * @return array
     */
    public function registerComponents()
    {
        return [
            'Lovata\Shopaholic\Components\CategoryList'   => 'CategoryList',
            'Lovata\Shopaholic\Components\CategoryPage'   => 'CategoryPage',
            'Lovata\Shopaholic\Components\CategoryData'   => 'CategoryData',
            'Lovata\Shopaholic\Components\Breadcrumbs'    => 'CatalogBreadcrumbs',
            'Lovata\Shopaholic\Components\CurrencyList'   => 'CurrencyList',
            'Lovata\Shopaholic\Components\ProductData'    => 'ProductData',
            'Lovata\Shopaholic\Components\ProductPage'    => 'ProductPage',
            'Lovata\Shopaholic\Components\ProductList'    => 'ProductList',
            'Lovata\Shopaholic\Components\BrandData'      => 'BrandData',
            'Lovata\Shopaholic\Components\BrandPage'      => 'BrandPage',
            'Lovata\Shopaholic\Components\BrandList'      => 'BrandList',
            'Lovata\Shopaholic\Components\PromoBlockData' => 'PromoBlockData',
            'Lovata\Shopaholic\Components\PromoBlockPage' => 'PromoBlockPage',
            'Lovata\Shopaholic\Components\PromoBlockList' => 'PromoBlockList',
        ];
    }

    /**
     * @return array
     */
    public function registerSettings()
    {
        return [
            'shopaholic-menu-main-settings' => [
                'label'       => 'lovata.shopaholic::lang.menu.main_settings',
                'description' => 'lovata.shopaholic::lang.menu.main_settings_description',
                'category'    => 'lovata.shopaholic::lang.tab.settings',
                'icon'        => 'oc-icon-book',
                'class'       => 'Lovata\Shopaholic\Models\Settings',
                'order'       => 100,
                'permissions' => [
                    'shopaholic-settings',
                ],
            ],
            'shopaholic-menu-currency'      => [
                'label'       => 'lovata.shopaholic::lang.menu.currency',
                'description' => 'lovata.shopaholic::lang.menu.currency_description',
                'category'    => 'lovata.shopaholic::lang.tab.settings',
                'icon'        => 'oc-icon-usd',
                'url'         => Backend::url('lovata/shopaholic/currencies'),
                'order'       => 1800,
                'permissions' => [
                    'shopaholic-menu-currency',
                ],
            ],
            'shopaholic-menu-tax'           => [
                'label'       => 'lovata.shopaholic::lang.menu.tax',
                'description' => 'lovata.shopaholic::lang.menu.tax_description',
                'category'    => 'lovata.shopaholic::lang.tab.settings',
                'icon'        => 'oc-icon-percent',
                'url'         => Backend::url('lovata/shopaholic/taxes'),
                'order'       => 1900,
                'permissions' => [
                    'shopaholic-menu-tax',
                ],
            ],
            'shopaholic-menu-price-types'   => [
                'label'       => 'lovata.shopaholic::lang.menu.price_type',
                'description' => 'lovata.shopaholic::lang.menu.price_type_description',
                'category'    => 'lovata.shopaholic::lang.tab.settings',
                'icon'        => 'oc-icon-money',
                'url'         => Backend::url('lovata/shopaholic/pricetypes'),
                'order'       => 2000,
                'permissions' => [
                    'shopaholic-menu-price-type',
                ],
            ],
            'shopaholic-menu-import-xml-file'   => [
                'label'       => 'lovata.shopaholic::lang.menu.import_xml_file',
                'description' => 'lovata.shopaholic::lang.menu.import_xml_file_description',
                'category'    => 'lovata.shopaholic::lang.tab.settings',
                'icon'        => 'oc-icon-download',
                'class'       => 'Lovata\Shopaholic\Models\XmlImportSettings',
                'order'       => 8000,
                'permissions' => [
                    'shopaholic-menu-import-xml-file',
                ],
            ],
            'shopaholic-menu-measure'      => [
                'label'       => 'lovata.shopaholic::lang.menu.measure',
                'description' => 'lovata.shopaholic::lang.menu.measure_description',
                'category'    => 'lovata.shopaholic::lang.tab.settings',
                'url'         => Backend::url('lovata/shopaholic/measures'),
                'icon'        => 'icon-balance-scale',
                'permissions' => ['shopaholic-menu-measure'],
                'order'       => 1650,
            ],
        ];
    }

    /**
     * Plugin boot method
     */
    public function boot()
    {
        $this->addEventListener();
    }

    /**
     * Add event listeners
     */
    protected function addEventListener()
    {
        Event::subscribe(ExtendMenuHandler::class);
        //Brand events
        Event::subscribe(BrandModelHandler::class);
        //Category events
        Event::subscribe(CategoryModelHandler::class);
        //Currency events
        Event::subscribe(CurrencyModelHandler::class);
        //Measure events
        Event::subscribe(MeasureModelHandler::class);
        //Offer events
        Event::subscribe(OfferModelHandler::class);
        Event::subscribe(ExtendOfferFieldsHandler::class);
        //Price events
        Event::subscribe(PriceModelHandler::class);
        //Product events
        Event::subscribe(ProductModelHandler::class);
        Event::subscribe(ProductRelationHandler::class);
        //Promo block events
        Event::subscribe(PromoBlockModelHandler::class);
        Event::subscribe(PromoBlockRelationHandler::class);
        //Tax events
        Event::subscribe(TaxModelHandler::class);
        Event::subscribe(TaxRelationHandler::class);
        Event::subscribe(ExtendTaxFieldsHandler::class);
    }

    /**
     * @return array
     */
    public function registerReportWidgets()
    {
        return [
            'Lovata\Shopaholic\Widgets\ImportFromXML' => [
                'label' => 'lovata.shopaholic::lang.widget.import_from_xml_files',
            ],
            'Lovata\Shopaholic\Widgets\ImportFromCSV' => [
                'label' => 'lovata.shopaholic::lang.widget.import_from_csv_files',
            ],
            'Lovata\Shopaholic\Widgets\ExportInCSV' => [
                'label' => 'lovata.shopaholic::lang.widget.export_in_csv_files',
            ],
        ];
    }
}
