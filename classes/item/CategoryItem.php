<?php namespace Lovata\Shopaholic\Classes\Item;

use Cms\Classes\Page as CmsPage;

use Kharanenka\Helper\CCache;

use Lovata\Toolbox\Classes\Item\ItemStorage;
use Lovata\Toolbox\Classes\Item\ElementItem;
use Lovata\Toolbox\Classes\Helper\PageHelper;

use Lovata\Shopaholic\Models\Settings;
use Lovata\Shopaholic\Models\Category;
use Lovata\Shopaholic\Classes\Collection\ProductCollection;
use Lovata\Shopaholic\Classes\Collection\CategoryCollection;

/**
 * Class CategoryItem
 * @package Lovata\Shopaholic\Classes\Item
 * @author  Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 *
 * @property                                                                                                                                   $id
 * @property string                                                                                                                            $name
 * @property string                                                                                                                            $slug
 * @property string                                                                                                                            $code
 * @property int                                                                                                                               $nest_depth
 * @property int                                                                                                                               $parent_id
 * @property int                                                                                                                               $product_count
 *
 * @property string                                                                                                                            $preview_text
 * @property \System\Models\File                                                                                                               $preview_image
 * @property \System\Models\File                                                                                                               $icon
 *
 * @property string                                                                                                                            $description
 * @property \October\Rain\Database\Collection|\System\Models\File[]                                                                           $images
 *
 * @property \October\Rain\Argon\Argon                                                                                                         $updated_at
 *
 * @property CategoryItem                                                                                                                      $parent
 *
 * @property array                                                                                                                             $children_id_list
 * @property CategoryCollection|CategoryItem[]                                                                                                 $children
 *
 * Properties for Shopaholic
 * @see     \Lovata\PropertiesShopaholic\Classes\Event\CategoryModelHandler::extendCategoryItem
 *
 * @property bool                                                                                                                              $inherit_property_set
 * @property array                                                                                                                             $property_set_id
 * @property \Lovata\PropertiesShopaholic\Classes\Collection\PropertySetCollection|\Lovata\PropertiesShopaholic\Classes\Item\PropertySetItem[] $property_set
 *
 * @property array                                                                                                                             $product_property_list
 * @property \Lovata\PropertiesShopaholic\Classes\Collection\PropertyCollection|\Lovata\PropertiesShopaholic\Classes\Item\PropertyItem[]       $product_property
 *
 * @property array                                                                                                                             $offer_property_list
 * @property \Lovata\PropertiesShopaholic\Classes\Collection\PropertyCollection|\Lovata\PropertiesShopaholic\Classes\Item\PropertyItem[]       $offer_property
 *
 * Filter for Shopaholic
 * @property \Lovata\FilterShopaholic\Classes\Collection\FilterPropertyCollection|\Lovata\PropertiesShopaholic\Classes\Item\PropertyItem[]     $product_filter_property
 * @property \Lovata\FilterShopaholic\Classes\Collection\FilterPropertyCollection|\Lovata\PropertiesShopaholic\Classes\Item\PropertyItem[]     $offer_filter_property
 *
 * VKontakte for Shopaholic
 * @property int                                                                                                                               $category_vk_id
 */
class CategoryItem extends ElementItem
{
    const MODEL_CLASS = Category::class;

    public static $arQueryWith = ['preview_image', 'icon', 'images'];

    /** @var Category */
    protected $obElement = null;

    public $arRelationList = [
        'parent'   => [
            'class' => CategoryItem::class,
            'field' => 'parent_id',
        ],
        'children' => [
            'class' => CategoryCollection::class,
            'field' => 'children_id_list',
        ],
    ];

    /**
     * Clear product count cache
     */
    public function clearProductCount()
    {
        $arCacheTag = [static::class];
        $sCacheKey = 'product_count_'.$this->id;

        CCache::clear($arCacheTag, $sCacheKey);
        ItemStorage::clear(static::class, $this->id);

        $obParentItem = $this->parent;
        if ($obParentItem->isEmpty()) {
            return;
        }

        $obParentItem->clearProductCount();
    }

    /**
     * Returns URL of a category page.
     *
     * @param string|null $sPageCode
     * @param array  $arRemoveParamList
     *
     * @return string
     */
    public function getPageUrl($sPageCode = null, $arRemoveParamList = [], $arAddParamList = [])
    {
        if (empty($sPageCode)) {
            $sPageCode = Settings::getValue('default_category_page_id', 'catalog');
        }

        //Get URL params
        $arParamList = $this->getPageParamList($sPageCode, $arRemoveParamList);

        if (!empty($arAddParamList)) {
            $arParamList = array_merge($arParamList, $arAddParamList);
        }

        //Generate page URL
        $sURL = CmsPage::url($sPageCode, $arParamList);

        return $sURL;
    }

    /**
     * Get URL param list by page code
     * @param string $sPageCode
     * @param array  $arRemoveParamList
     * @return array
     */
    public function getPageParamList($sPageCode, $arRemoveParamList = []) : array
    {
        $arResult = [];
        if (!empty($arRemoveParamList)) {
            foreach ($arRemoveParamList as $sParamName) {
                $arResult[$sParamName] = null;
            }
        }

        //Get all slug params
        $arParamList = PageHelper::instance()->getUrlParamList($sPageCode, null);
        if (!empty($arParamList)) {
            foreach ($arParamList as $sParamName) {
                $arResult[$sParamName] = null;
            }
        }

        //Get URL params for page
        $arParamList = PageHelper::instance()->getUrlParamList($sPageCode, 'CategoryPage');
        if (empty($arParamList)) {
            return [];
        }

        //Get slug list
        $arSlugList = $this->getSlugList();

        $arWildcardParamList = PageHelper::instance()->getUrlParamList($sPageCode, 'CategoryPage', 'slug', true);
        if (!empty($arWildcardParamList)) {
            $arSlugList = array_reverse($arSlugList);
            $arResult[array_shift($arWildcardParamList)] = implode('/', $arSlugList);

            return $arResult;
        } elseif (count($arParamList) == 1) {
            $sParamName = array_shift($arParamList);
            $arResult[$sParamName] = array_shift($arSlugList);

            return $arResult;
        }

        //Prepare page property list
        $arSlugList = array_reverse($arSlugList);
        $arParamList = array_reverse($arParamList);
        foreach ($arParamList as $sParamName) {
            if (!empty($arSlugList)) {
                $arResult[$sParamName] = array_shift($arSlugList);
            }
        }

        return $arResult;
    }

    /**
     * Get array with categories slugs
     * @return array
     */
    protected function getSlugList() : array
    {
        $arResult = [$this->slug];

        $obParentCategory = $this->parent;
        while ($obParentCategory->isNotEmpty()) {
            $arResult[] = $obParentCategory->slug;
            $obParentCategory = $obParentCategory->parent;
        }

        return $arResult;
    }

    /**
     * Set element data from model object
     *
     * @return array
     */
    protected function getElementData()
    {
        $arResult = [
            'nest_depth' => $this->obElement->getDepth(),
        ];

        $arResult['children_id_list'] = $this->obElement->children()
            ->active()
            ->orderBy('nest_left', 'asc')
            ->lists('id');

        return $arResult;
    }

    /**
     * Get product count for category
     * @return int
     */
    protected function getProductCountAttribute()
    {
        $iProductCount = $this->getAttribute('product_count');
        if ($iProductCount !== null) {
            return $iProductCount;
        }

        //Get product count from cache
        $arCacheTag = [static::class];
        $sCacheKey = 'product_count_'.$this->id;

        $iProductCount = CCache::get($arCacheTag, $sCacheKey);
        if ($iProductCount !== null) {
            return $iProductCount;
        }

        //Calculate product count from child categories
        $iProductCount = 0;
        $obChildCategoryCollect = $this->children;
        if ($obChildCategoryCollect->isNotEmpty()) {
            /** @var CategoryItem $obChildCategoryItem */
            foreach ($obChildCategoryCollect as $obChildCategoryItem) {
                if ($obChildCategoryItem->isEmpty()) {
                    continue;
                }

                $iProductCount += $obChildCategoryItem->product_count;
            }
        }

        $iProductCount += ProductCollection::make()->active()->category($this->id)->count();

        CCache::forever($arCacheTag, $sCacheKey, $iProductCount);
        $this->setAttribute('product_count', $iProductCount);

        return $iProductCount;
    }
}
