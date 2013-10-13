<?php

/**
 * This file is part of the Grido (http://grido.bugyik.cz)
 *
 * Copyright (c) 2011 Petr Bugyík (http://petr.bugyik.cz)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Grido\Components\Filters;

/**
 * Text input filter.
 *
 * @package     Grido
 * @subpackage  Components\Filters
 * @author      Petr Bugyík
 */
class Text extends Filter
{
    /** @var mixed */
    protected $suggestionColumn;

    /** @var string */
    protected $condition = 'LIKE ?';

    /** @var string */
    protected $formatValue = '%%value%';

    /**
     * Allows suggestion.
     * @param mixed $column
     * @return Text
     */
    public function setSuggestion($column = NULL)
    {
        $this->suggestionColumn = $column;

        $prototype = $this->getControl()->controlPrototype;
        $prototype->attrs['autocomplete'] = 'off';
        $prototype->class[] = 'suggest';

        $filter = $this;
        $this->grid->onRender[] = function(\Grido\Grid $grid) use ($prototype, $filter) {
            $replacement = '-query-';
            $prototype->data['grido-suggest-replacement'] = $replacement;
            $prototype->data['grido-suggest-handler'] = $filter->link('suggest!', array(
                'query' => $replacement)
            );
        };

        return $this;
    }

    /**********************************************************************************************/

    /**
     * @internal - Do not call directly.
     * @param string $query - value from input
     */
    public function handleSuggest($query)
    {
        if (!$this->grid->presenter->isAjax()) {
            $this->presenter->terminate();
        }

        $actualFilter = $this->grid->getActualFilter();
        if (isset($actualFilter[$this->name])) {
            unset($actualFilter[$this->name]);
        }
        $conditions = $this->grid->__getConditions($actualFilter);
        $conditions[] = $this->__getCondition($query);

        $column = $this->suggestionColumn ? $this->suggestionColumn : current($this->getColumn());
        $items = $this->grid->model->suggest($column, $conditions);

        print \Nette\Utils\Json::encode($items);
        $this->grid->presenter->terminate();
    }

    /**
     * @return \Nette\Forms\Controls\TextInput
     */
    protected function getFormControl()
    {
        $control = new \Nette\Forms\Controls\TextInput($this->label);
        $control->controlPrototype->class[] = 'text';

        return $control;
    }
}
