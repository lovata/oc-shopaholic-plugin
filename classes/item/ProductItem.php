<?php namespace Lovata\Shopaholic\Classes\Item;

use Cms\Classes\Page as CmsPage;

use Lovata\Toolbox\Classes\Item\ElementItem;
use Lovata\Toolbox\Classes\Helper\PageHelper;

use Lovata\Shopaholic\Models\Settings;
use Lovata\Shopaholic\Models\Product;
use Lovata\Shopaholic\Classes\Collection\CategoryCollection;
use Lovata\Shopaholic\Classes\Collection\OfferCollection;

/**
 * Class ProductItem
 * @package Lovata\Shopaholic\Classes\Item
 * @author  Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 *
 * @property int                                                                                                                         $id
 * @property bool                                                                                                                        $active
 * @property bool                                                                                                                        $trashed
 * @property string                                                                                                                      $name
 * @property string                                                                                                                      $slug
 * @property string                                                                                                                      $code
 *
 * @property int                                                                                                                         $category_id
 * @property CategoryItem                                                                                                                $category
 * @property array                                                                                                                       $additional_category_id
 * @property CategoryCollection|CategoryItem[]                                                                                           $additional_category
 *
 * @property int                                                                                                                         $brand_id
 * @property BrandItem                                                                                                                   $brand
 *
 * @property string                                                                                                                      $preview_text
 * @property \System\Models\File                                                                                                         $preview_image
 *
 * @property string                                                                                                                      $description
 * @property \October\Rain\Database\Collection|\System\Models\File[]                                                                     $images
 *
 * @property array                                                                                                                       $offer_id_list
 * @property OfferCollection|OfferItem[]                                                                                                 $offer
 *
 * Properties for Shopaholic
 * @see     \Lovata\PropertiesShopaholic\Classes\Event\ProductModelHandler::extendProductItem
 * @property array                                                                                                                       $property_value_array
 * @property \Lovata\PropertiesShopaholic\Classes\Collection\PropertyCollection|\Lovata\PropertiesShopaholic\Classes\Item\PropertyItem[] $property
 *
 * Reviews for Shopaholic
 * @see     \Lovata\ReviewsShopaholic\Classes\Event\ProductModelHandler::extendProductItem
 * @property float                                                                                                                       $rating
 * @property array                                                                                                                       $rating_data [1 => 0, 2 => 4, 3 => 7, 4 => 21, 5 => 48]
 * @property \Lovata\ReviewsShopaholic\Classes\Collection\ReviewCollection|\Lovata\ReviewsShopaholic\Classes\Item\ReviewItem[]           $review
 *
 * @method int getRatingCount(int $iRating)
 * @method int getRatingPercent(int $iRating)
 * @method int getRatingTotalCount()
 *
 * Related products for Shopaholic
 * @property \Lovata\Shopaholic\Classes\Collection\ProductCollection|ProductItem[]                                                       $related
 *
 * Accessories for Shopaholic
 * @property \Lovata\Shopaholic\Classes\Collection\ProductCollection|ProductItem[]                                                       $accessory
 *
 * Labels for Shopaholic
 * @property \Lovata\LabelsShopaholic\Classes\Collection\LabelCollection|\Lovata\LabelsShopaholic\Classes\Item\LabelItem[]               $label
 *
 * Compare for Shopaholic
 * @method bool inCompare()
 *
 * Wish list for Shopaholic
 * @method bool inWishList()
 *
 * YandexMarket for Shopaholic
 * @property \System\Models\File                                                                                                         $preview_image_yandex
 * @property \October\Rain\Database\Collection|\System\Models\File[]                                                                     $images_yandex
 *
 * Facebook for Shopaholic
 * @property \System\Models\File                                                                                                         $preview_image_facebook
 * @property \October\Rain\Database\Collection|\System\Models\File[]                                                                     $images_facebook
 *
 * VKontakte for Shopaholic
 * @property bool                                                                                                                        $active_vk
 * @property int                                                                                                                         $external_vk_id
 * @property \System\Models\File                                                                                                         $preview_image_vkontakte
 * @property \October\Rain\Database\Collection|\System\Models\File[]                                                                     $images_vkontakte
 *
 * Downloadable file for Shopaholic
 * @property bool                                                                                                                        $is_file_access
 */
class ProductItem extends ElementItem
{
    const MODEL_CLASS = Product::class;

    public static $arQueryWith = [
        'preview_image',
        'images',
        'additional_category',
        'offer',
        'offer.preview_image',
        'offer.images',
        'offer.main_price',
        'offer.price_link'
    ];

    /** @var Product */
    protected $obElement = null;

    public $arRelationList = [
        'offer'               => [
            'class' => OfferCollection::class,
            'field' => 'offer_id_list',
        ],
        'category'            => [
            'class' => CategoryItem::class,
            'field' => 'category_id',
        ],
        'additional_category' => [
            'class' => CategoryCollection::class,
            'field' => 'additional_category_id',
        ],
        'brand'               => [
            'class' => BrandItem::class,
            'field' => 'brand_id',
        ],
    ];

    /**
     * Check element, active == true, trashed == false
     * @return bool
     */
    public function isActive()
    {
        return $this->active && !$this->trashed;
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
            $sPageCode = Settings::getValue('default_product_page_id', 'product');
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
        $arPageParamList = [];
        if (!empty($arRemoveParamList)) {
            foreach ($arRemoveParamList as $sParamName) {
                $arResult[$sParamName] = null;
            }
        }

        //Get URL params for categories
        $aCategoryParamList = $this->category->getPageParamList($sPageCode);
        $aBrandParamList = $this->brand->getPageParamList($sPageCode);

        //Get URL params for page
        $arParamList = (array) PageHelper::instance()->getUrlParamList($sPageCode, 'ProductPage');
        if (!empty($arParamList)) {
            $sPageParam = array_shift($arParamList);
            $arPageParamList[$sPageParam] = $this->slug;
        }

        $arResult = array_merge($arResult, $aCategoryParamList, $aBrandParamList, $arPageParamList);

        return $arResult;
    }

    /**
     * Set element data from model object
     * @return array
     */
    protected function getElementData()
    {
        $arResult = [
            'offer_id_list'          => $this->obElement->offer->where('active', true)->pluck('id')->all(),
            'additional_category_id' => $this->obElement->additional_category->pluck('id')->all(),
            'trashed'                => $this->obElement->trashed(),
        ];

        foreach ($this->obElement->offer as $obOffer) {
            OfferItem::make($obOffer->id, $obOffer);
        }

        return $arResult;
    }
}
