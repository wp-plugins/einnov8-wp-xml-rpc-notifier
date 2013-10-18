<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yipeecaiey
 * Date: 10/18/13
 * Time: 2:35 PM
 * To change this template use File | Settings | File Templates.
 */
class ei8XmlrpcFloodgateForm
{
    public $action;
    public $method;
    public $body;
    public $submitButton;

    public function make_field_hidden($var,$val) {
        return sprintf('<input type="hidden" name="%s" value="%s" />', $var, $val);
    }

    public function make_field_password($var,$size=65) {
        return sprintf('<input type="password" name="%s" size=%d />', $var, $size);
    }

    public function make_field_text($var,$val,$size=65) {
        return sprintf('<input type="text" name="%s" size=%d value="%s" />', $var, $size, $val);
    }

    public function make_field_textarea($var,$val,$rows='',$cols=65) {
        $showRows = ($rows=='') ? '' : 'rows="'.$rows.'"';
        return sprintf('<textarea class="ei8-textarea" cols=%d name="%s" %s>%s</textarea>', $cols, $var, $showRows, $val);
    }

    public function make_field_submit() {
        return ($this->submitButton) ? sprintf('<p class="submit"><input type="submit" class="button-primary" value="%s" /></p>', $this->submitButton) : '' ;
    }

    public function make_field_boolean($var,$val) {
        $selectNo  = ($val!=1) ? "SELECTED" : "" ;
        $selectYes = ($val==1) ? "SELECTED" : "" ;
        $html = "<select name='$var'>
                        <option value='2' $selectNo>No</option>
                        <option value=1 $selectYes>Yes</option>
                    </select>";
        return $html;
    }
    
    public function redirect($url) {
        
        if ( !headers_sent() ) {
            wp_redirect($url);
        } else {
            $url = site_url($url);
?>

<meta http-equiv="Refresh" content="0; URL=<?php echo $url; ?>">
<script type="text/javascript">
    <!--
    document.location.href = "<?php echo $url; ?>"
    //-->
</script>
</head>
<body>
Sorry. Please use this <a href="<?php echo $url; ?>" title="New Post">link</a>.
</body>
</html>

<?php
        }
        exit();
    }

    public function validate_emails($email) {
        if(strstr($email,',')) {
            $emails = explode(',',$email);
            foreach ($emails as $piece) {
                if (!$this->validate_email(trim($piece))) return false;
            }
            return true;
        } else return $this->validate_email($email);
    }

    public function validate_email($email) {
        return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i", $email);
    }

    public function validate_url($url) {
        if(strstr($url, ' ')) return false;
        return preg_match('/^http(s?):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $url);
    }

}

class ei8XmlrpcFloodgateFormFG extends ei8XmlrpcFloodgateForm
{
    public function __construct($action='',$method='') {
        $this->submitButton = 'Submit';
        if($action!='') $this->action = $action;
        if($method!='') $this->action = $method;
    }

    public function process() {
    }

    public function build_table($fields) {
        $table = '<table class="form-table">';
        foreach($fields as $var=>$arr) {
            $type   = array_shift($arr);
            $title = ($type=='hidden') ? '' : array_shift($arr) ;
            $showField = $this->field_maker($type,$this->prep_var_name($var),$arr);
            $table .= ($type=='hidden') ? $showField : sprintf('<tr valign="top"><th scope="row">%s: </th><td>%s</td></tr>', $title, $showField);
        }
        $table .= '</table>';
        return $table;
    }

    public function prep_var_name($var) {
        return 'ei8_floodgate_form_var_'.$var;
    }

    public function field_maker($type,$var,$args) {
        //list($type,$val,$extra1,$extra2) = $args;
        $method = 'make_field_'.$type;
        //if(empty($extra2)) array_pop($args);
        //if(empty($extra1)) array_pop($args);
        switch($type) {
            case 'hidden':
            case 'password':
            case 'boolean':
                $field = $this->$method($var, $args[0]);
                break;
            case 'text':
                $field = $this->$method($var, $args[0], $args[1]);
                break;
            case 'textarea':
                $field = $this->$method($var, $args[0], $args[1], $args[2]);
                break;
            default:
                $field = '<p>SCRIPT ERROR ($var)</p>';
                break;
        }
        //$field = '<p>numArgs('.count($args).')</p>'.$field;
        return $field;
    }

    public function render() {
        $showAction = (!empty($this->action)) ? sprintf('action="%s"',$this->action) : '' ;
        $showMethod = (!empty($this->method)) ? sprintf('method="%s"',$this->method) : '' ;
        $showSubmit = $this->make_field_submit();
        $html =<<<EOT
        <form method="post" $showAction $showMethod>
            $this->body
            $showSubmit
        </form>
EOT;
        return $html;
    }
}
