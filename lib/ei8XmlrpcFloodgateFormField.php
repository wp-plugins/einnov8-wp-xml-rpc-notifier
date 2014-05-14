<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yipeecaiey
 * Date: 11/05/13
 * Time: 2:35 PM
 * To change this template use File | Settings | File Templates.
 */
class ei8XmlrpcFloodgateFormField
{
    const VAR_PRE = 'ei8_floodgate_form_var_';

    public $label;
    public $var;
    public $var_form;
    //public $var_pre = 'ei8_floodgate_form_var_';
    public $value;
    public $default_value;
    public $type;
    public $required;
    public $error;
    public $error_message;
    public $hint;

    public $line_open;

    public function __construct($var,$value='',$args='') {
        $this->var = $var;
        $this->var_form = $this->prep_var_name($var);
        $this->set_value($value);
        if($args!='' && is_array($args)) foreach($args as $key=>$val) $this->$key = $val;
    }

    public function do_type($type) {
        switch($type) {
            case 'boolean':
                return new ei8XmlrpcFloodgateFormFieldSelectBoolean($this->var,$this->value);
            case 'hidden':
                return new ei8XmlrpcFloodgateFormFieldHidden($this->var,$this->value);
            case 'password':
                return new ei8XmlrpcFloodgateFormFieldPassword($this->var,$this->value);
            case 'text':
                return new ei8XmlrpcFloodgateFormFieldText($this->var,$this->value);
            case 'textarea':
                return new ei8XmlrpcFloodgateFormFieldTextarea($this->var,$this->value);
            default:
                return true;
        }
    }

    public function validate() {
        if($this->is_required() && !$this->is_empty()) return $this->throw_error();
        return true;
    }

    public function is_empty($errorOnEmpty='') {
        if($errorOnEmpty!='') $this->error = (boolean) $this->value;
        return (boolean) $this->value;
    }

    public function is_required() {
        return (boolean) $this->required;
    }

    public static function prep_var_name($var) {
        //if(!strstr($var,$this->var_pre)) $var = $this->var_pre.$var;
        if(!strstr($var,self::VAR_PRE)) $var = self::VAR_PRE.$var;
        return $var;
    }

    public function render() {
        return $this->render_label().$this->render_field();
    }

    public function render_field() {
        return "FORM FIELD GOES HERE";
    }

    public function render_label() {
        return sprintf('<label for="%s">%s</label>', $this->var, $this->label);
    }

    public function set_value($value) {
        $this->value = $value;
    }

    public function throw_error() {
        $this->error = true;
        return false;
    }
}

class ei8XmlrpcFloodgateFormFieldText extends ei8XmlrpcFloodgateFormField
{
    public $size = 65;
    public $type = 'text';

    public function render_field() {
        return sprintf('<input type="text" name="%s" id="%s" size=%d value="%s" />', $this->var_form, $this->var_form, $this->size, $this->value);
    }
}

class ei8XmlrpcFloodgateFormFieldTextEmail extends ei8XmlrpcFloodgateFormFieldText
{
    public function validate() {
        $emails = (strstr($this->value,',')) ? explode(',',$this->value) : (array) $this->value ;
        foreach ($emails as $piece) if (!$this->validate_email(trim($piece))) return $this->throw_error();
        return parent::validate();
    }

    public function validate_email($email) {
        return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i", $email);
    }
}

class ei8XmlrpcFloodgateFormFieldTextUrl extends ei8XmlrpcFloodgateFormFieldText
{
    public function validate() {
        if(!$this->validate_url($this->value)) return $this->throw_error();
        return parent::validate();
    }

    public function validate_url($url) {
        return preg_match('/^http(s?):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', trim($url));
    }
}

class ei8XmlrpcFloodgateFormFieldPassword extends ei8XmlrpcFloodgateFormFieldText
{
    public $type = 'password';

    public function render_field() {
        return sprintf('<input type="password" name="%s" id="%s" size=%d value="%s" />', $this->var_form, $this->var_form, $this->size, $this->value);
    }
}

class ei8XmlrpcFloodgateFormFieldHidden extends ei8XmlrpcFloodgateFormField
{
    public $type = 'hidden';

    public function render_field() {
        return sprintf('<input type="hidden" name="%s" value="%s" />', $this->var_form, $this->value);
    }

    public function render_label() {}
}

class ei8XmlrpcFloodgateFormFieldTextarea extends ei8XmlrpcFloodgateFormField
{
    public $type = 'textarea';

    public $rows;
    public $cols=65;

    public function render_field() {
        $showRows = (!empty($this->rows)) ? 'rows="'.$this->rows.'"' : '' ;
        return sprintf('<textarea class="ei8-textarea" cols=%d name="%s" id=""%s" %s>%s</textarea>', $this->cols, $this->var_form, $this->var_form, $showRows, $this->value);
    }
}

class ei8XmlrpcFloodgateFormFieldSubmit extends ei8XmlrpcFloodgateFormField
{
    public $type = 'submit';

    /*public function __construct($var,$value='',$args='') {
        parent::__construct($var,$value,$args);
        if(!empty($this->label)) $this->value = $this->label;
        $this->value = 'Boo';
    }*/

    public function render_field() {
        //echo "<p>Submit Field:<pre>"; print_r($this); echo "</pre></p>"; exit;
        return sprintf('<div class="submit"><input type="submit" class="button-primary" value="%s" /></p>', $this->value);
    }

    public function render_label() {}
}

class ei8XmlrpcFloodgateFormFieldOption
{
    public $title;
    public $value;
    public $selected;

    public function __construct($title='',$value='',$selected='') {
        $this->title = $title;
        $this->value = $value;
        $this->selected = (boolean) $selected;
    }

    public function is_selected() {
        return (boolean) $this->selected;
    }
}

class ei8XmlrpcFloodgateFormFieldSelect extends ei8XmlrpcFloodgateFormField
{
    public $type = 'select';
    public $default = '';
    public $options;

    public function __construct($var,$value='',$options='',$args='') {
        $this->options = (array) $options;
        parent::__construct($var,$value,$args);
    }

    public function add_option($title,$value,$selected='') {
        //handle default selections
        if($selected=='' && $this->default!='') $selected = ($title==$this->default);
        $this->options[] = new ei8XmlrpcFloodgateFormFieldOption($title,$value,$selected);
    }

    public function render_field() {
        $html = sprintf("<select name='%s' id='%s'>",$this->var_form,$this->var_form);
        foreach($this->options as $option) {
            $selected = ($option->is_selected()) ? "SELECTED" : "" ;
            $html .= sprintf("<option value='%s' %s>%s</option>", $option->value, $selected, $option->title);
        }
        $html .= "</select>";
        return $html;
    }

    public function set_default($var) {
        $this->default = $var;
    }

    public function set_value($value) {
        parent::set_value($value);
        $vals = (array) $value;
        foreach($this->options as $option) $option->selected = (in_array($option->value,$vals)) ;
    }
}

class ei8XmlrpcFloodgateFormFieldSelectBoolean extends ei8XmlrpcFloodgateFormFieldSelect
{
    public $type = 'boolean';

    public function __construct($var,$value='',$args='',$default='No') {
        $this->set_default($default);
        $this->add_option("No",'2');
        $this->add_option("Yes",'1');
        parent::__construct($var,$value,$this->options,$args);
    }
}
