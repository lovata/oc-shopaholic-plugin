<?php namespace Lovata\Shopaholic\Components;

use Event;
use Lovata\Shopaholic\Classes\Item\BrandItem;
use Lovata\Toolbox\Components\ElementPage;
use Lovata\Shopaholic\Models\Brand;

/**
 * Class BrandPage
 * @package Lovata\Shopaholic\Components
 * @author Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class BrandPage extends ElementPage
{
    /**
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'          => 'lovata.shopaholic::lang.component.brand_page_name',
            'description'   => 'lovata.shopaholic::lang.component.brand_page_description',
        ];
    }

    /**
     * Get element object
     * @param string $sElementSlug
     * @return Brand
     */
    protected function getElementObject($sElementSlug)
    {
        if(empty($sElementSlug)) {
            return null;
        }

        return Brand::active()->getBySlug($sElementSlug)->first();
    }

    /**
     * Make new element item
     * @param int $iElementID
     * @param Brand $obElement
     * @return BrandItem
     */
    protected function makeItem($iElementID, $obElement)
    {
        return BrandItem::make($iElementID, $obElement);
    }

    /**
     * @return \Illuminate\Http\Response|null
     */
    public function onRun()
    {
        $obResult = parent::onRun();
        if($obResult === null) {

            //Send event
            Event::fire('shopaholic.brand.open', [$this->obElement]);
        }

        return $obResult;
    }
}