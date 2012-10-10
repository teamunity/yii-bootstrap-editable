<?php
/**
 * EditableDetailView class file.
 * 
 * This widget makes editable several attributes of single model, shown as name-value table
 * 
 * @author Vitaliy Potapov <noginsk@rambler.ru>
 * @link https://github.com/vitalets/yii-bootstrap-editable
 * @copyright Copyright &copy; Vitaliy Potapov 2012
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version 1.0.0
 */
 
Yii::import('ext.yii-bootstrap-editable.EditableField');
Yii::import('zii.widgets.CDetailView');

class EditableDetailView extends CDetailView
{
    //common url for all editables
    public $url = '';
    public $numColumns = 1;
    //set bootstrap css
    protected $defaultHtmlOptions =  array('class'=> 'table table-bordered table-striped table-hover table-condensed');
    public $htmlOptions = array();

    public function init()
    {
        if (!$this->data instanceof CModel) {
            throw new CException('Property "data" should be of CModel class.');
        }

        parent::init();
        $classes = explode(" ", $this->defaultHtmlOptions['class']);
        EBootstrap::mergeClass($this->htmlOptions, $classes);

    }

    protected function renderItem($options, $templateData)
    {
        //if editable set to false --> not editable
        $isEditable = array_key_exists('editable', $options) && $options['editable'] !== false;

        //if name not defined or it is not safe --> not editable
        $isEditable = !empty($options['name']) && $this->data->isAttributeSafe($options['name']);

        if ($isEditable) {    
            //ensure $options['editable'] is array
            if(!array_key_exists('editable', $options) || !is_array($options['editable'])) $options['editable'] = array();

            //take common url
            if (!array_key_exists('url', $options['editable'])) {
                $options['editable']['url'] = $this->url;
            }

            $editableOptions = CMap::mergeArray($options['editable'], array(
                'model'     => $this->data,
                'attribute' => $options['name'],
                'emptytext' => ($this->nullDisplay === null) ? Yii::t('zii', 'Not set') : strip_tags($this->nullDisplay),
            ));
            
            //if value in detailview options provided, set text directly
            if(array_key_exists('value', $options) && $options['value'] !== null) {
                $editableOptions['text'] = $templateData['{value}'];
                $editableOptions['encode'] = false;
            }

            $templateData['{value}'] = $this->controller->widget('EditableField', $editableOptions, true);
        } 

        parent::renderItem($options, $templateData);
    }

    public function run(){
        $this->_run();
    }
    protected function getNumColumns(){
        $numColumns = 1;
        foreach($this->attributes as $attribute){
            if (array_key_exists('columnNumber', $attribute)){
                $numColumns = $attribute['columnNumber'] >= $numColumns ? $attribute['columnNumber'] + 1 : $numColumns;
            }
        }
        return $numColumns;
    }
    public function _run()
    {
       
        $numColumns = $this->getNumColumns();
        $colSpan = 12 / $numColumns;
           echo JQBootstrap::openRow(true);
            for($i=0;$i<$numColumns;$i++){
                echo JQBootstrap::openColumn($colSpan);
                if ($numColumns == 1){
                    $cNum = -1;
                }
                else 
                    $cNum = $i;
                $this->renderColGroup($cNum);
                echo JQBootstrap::closeColumn();
            }

        echo JQBootstrap::closeRow();
        
    }
    /**
     *  Render Attributes in a Bootstrap Column Grouping
     * */
    public function renderColGroup($columnNumber=0){
         $formatter=$this->getFormatter();
       if ($this->tagName!==null){
            $this->htmlOptions['id'] = uniqid();
            echo CHtml::openTag($this->tagName,$this->htmlOptions);
        }
        $i=0;
        $n=is_array($this->itemCssClass) ? count($this->itemCssClass) : 0;
                        
        foreach($this->attributes as $attribute)
        {   if ($attribute['columnNumber'] == $columnNumber || $columnNumber == -1) {
            if(is_string($attribute))
            {
                if(!preg_match('/^([\w\.]+)(:(\w*))?(:(.*))?$/',$attribute,$matches))
                    throw new CException(Yii::t('zii','The attribute must be specified in the format of "Name:Type:Label", where "Type" and "Label" are optional.'));
                $attribute=array(
                    'name'=>$matches[1],
                    'type'=>isset($matches[3]) ? $matches[3] : 'text',
                );
                if(isset($matches[5]))
                    $attribute['label']=$matches[5];
            }
            
            if(isset($attribute['visible']) && !$attribute['visible'])
                continue;

            $tr=array('{label}'=>'', '{class}'=>$n ? $this->itemCssClass[$i%$n] : '');
            if(isset($attribute['cssClass']))
                $tr['{class}']=$attribute['cssClass'].' '.($n ? $tr['{class}'] : '');

            if(isset($attribute['label']))
                $tr['{label}']=$attribute['label'];
            else if(isset($attribute['name']))
            {
                if($this->data instanceof CModel)
                    $tr['{label}']=$this->data->getAttributeLabel($attribute['name']);
                else
                    $tr['{label}']=ucwords(trim(strtolower(str_replace(array('-','_','.'),' ',preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $attribute['name'])))));
            }

            if(!isset($attribute['type']))
                $attribute['type']='text';
            if(isset($attribute['value']))
                $value=$attribute['value'];
            else if(isset($attribute['name']))
                $value=CHtml::value($this->data,$attribute['name']);
            else
                $value=null;

            $tr['{value}']=$value===null ? $this->nullDisplay : $formatter->format($value,$attribute['type']);

            $this->renderItem($attribute, $tr);

            $i++;
            }
        }

        if ($this->tagName!==null)
            echo CHtml::closeTag($this->tagName);
    }

}


