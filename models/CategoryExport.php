<?php namespace Lovata\Shopaholic\Models;

use Lovata\Toolbox\Classes\Helper\AbstractExportModelInCSV;

/**
 * Class CategoryExport
 *
 * @package Lovata\Shopaholic\Models
 * @author  Sergey Zakharevich, s.zakharevich@lovata.com, LOVATA Group
 *
 * @mixin \October\Rain\Database\Builder
 * @mixin \Eloquent
 */
class CategoryExport extends AbstractExportModelInCSV
{
    /** @var string */
    public $table = 'lovata_shopaholic_categories';

    /**
     * Get item list.
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Category[]
     */
    protected function getItemList()
    {
        return Category::get();
    }
}
