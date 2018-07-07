<?php

/**
 * @see http://www.yiiframework.com/doc-2.0/yii-widgets-linksorter.html
 */

namespace Kubik\yii2;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\Html;
use yii\web\Request;

class ButtonDropdownSorter extends Widget
{
	public $label;
    
    public $sort;
	
    public $attributes;
    
    public $options = ['class' => 'sorter'];

    public $urlManager;

    public $sortParam = 'sort';

    public $absolute = false;

    public $pjax = false;

    public $ajaxId;

    public $urldecode = false;



    /**
     * Initializes the sorter.
     */
    public function init()
    {
        $this->sort->attributes = $this->attributes;
        if ($this->sort === null) {
            throw new InvalidConfigException('The "sort" property must be set.');
        }
    }

    /**
     * Executes the widget.
     * This method renders the sort links.
     */
    public function run()
    {
        echo $this->renderSortButtonDropdown();
    }

    /**
     * Renders the sort Dropdown
     * @return string the rendering result
     */
    protected function renderSortButtonDropdown()
    {
        $attributes = empty($this->attributes) ? array_keys($this->attributes) : $this->attributes;
        $list=[];
        if (empty($this->label))
        	$this->label = 'Sort';
        foreach ($attributes as $attribute=>$options) {
            $directions = $this->sort->getAttributeOrders();
            if (($params = $this->sort->params) === null) {
                $request = Yii::$app->getRequest();
                $params = $request instanceof Request ? $request->getQueryParams() : [];
            }
            $params[0] = $this->sort->route === null ? Yii::$app->controller->getRoute() : $this->sort->route;
            if (isset($directions[$attribute])) {
                if($directions[$attribute] === SORT_DESC){
                    $params[$this->sortParam] = $attribute;
                    $list[$this->createUrl($params)] = key($options['asc']);
                    $params[$this->sortParam] = '-'.$attribute;
                    $value = $this->createUrl($params);
                    $list[$value] = key($options['desc']);
                }else{
                    $params[$this->sortParam] = $attribute;
                    $value = $this->createUrl($params);
                    $list[$value] = key($options['asc']);
                    $params[$this->sortParam] = '-'.$attribute;
                    $list[$this->createUrl($params)] = key($options['desc']);
                }

                unset($directions[$attribute]);
            }else{
                $params[$this->sortParam] = $attribute;
                $list[$this->createUrl($params)] = key($options['asc']);
                $params[$this->sortParam] = '-'.$attribute;
                $list[$this->createUrl($params)] = key($options['desc']);
            }
        };
        $this->functionJS();
        $options = ['text' => 'Please select', 'prompt'=>'Сортировка', 'options' => ['class' => 'prompt', 'label' => 'Select'],
                        'onchange'=>'sort(this, this.options[this.selectedIndex].value)'
                    ];
        echo Html::dropDownList('dropSort', !empty($value) ? $value : '', $list, $options);
	}
    public function createUrl($params)
    {
        $urlManager = $this->urlManager === null ? Yii::$app->getUrlManager() : $this->urlManager;
        if ($this->absolute) {
            return $this->urldecode ? urldecode(stristr($urlManager->createAbsoluteUrl($params), '?', true)).stristr($urlManager->createAbsoluteUrl($params), '?') : $urlManager->createAbsoluteUrl($params);
        } else {
            return $this->urldecode ? urldecode(stristr($urlManager->createUrl($params), '?', true)).stristr($urlManager->createUrl($params), '?') : $urlManager->createUrl($params);
        }
    }
    public function functionJS(){
        if($this->pjax){
            $pjaxId = !empty($this->pjaxId) ? $this->pjaxId : '$(list).closest("[data-pjax-container]").attr("id")';
            $js_text = '$.pjax({url: url, replace: false, scrollTo: true, container: "#"+'.$pjaxId.'})';
        }else{
            $js_text = 'document.location=this.options[this.selectedIndex].value';
        }
        $js = <<<JS
            function sort(list, url) {
                $js_text;
            }
JS;
        $this->view->registerJs($js, yii\web\View::POS_END);
    }
}
