<?php
/**
error_reporting(E_ALL);
$config=array(
        'reader'=>array('read'),
        'author'=>array('create','reader', 'edit'=>'return $author_id==$user_id;'),        
        'editor'=>array('author','edit','publish'),
        'master'=>array('editor','delete'),

        'create'=>array('createPost','createComment')
);
$ar=new AuthRelationship($config);
echo (int)$ar->checkAccess('master', 'read', array('author_id'=>1, 'user_id'=>2));
*/

/**
 * @author axgle 2011-1-27
 * @version 0.0.1
 */
class AuthRelationship {
    private $_config=array();
    private $_access;
    /**
     $config=array(
     'author'=>array('create','read', 'edit'=>'return $author_id==$user_id;'),
     'reader'=>array('read'),
     'editor'=>array('author','edit','publish'),
     'master'=>array('editor','delete'),
     );
     * @param <array> $config
     */
    function  __construct($config=array()) {
        $this->setConfig($config);
    }
    function setConfig($config=array()) {
        $this->_config=$config;
    }

    function checkAccess($role,$operation,$params=array()) {
        $key=$role.'_'.$operation.md5(serialize($params));
        if(isset ($this->_access[$key])) return $this->_access[$key];
        return $this->_access[$key]=$this->_check($role, $operation, $params);
    }
    function _check($role,$operation,$params=array()) {        
        if(!isset($this->_config[$role]))  {			
			return false;
		}
		$children = $this->_config[$role];
        if($role==$operation)      return true;
        foreach($children as $key => $value) {
            if(is_integer($key)) {
                $child=$value;
                $bizrule='';
            }else {
                $child=$key;
                $bizrule=$value;
            }
            if($operation==$child) {
                if($bizrule=='') return true;
                extract($params);
                return eval($bizrule)!=0;
            }else {
                if($this->_check($child, $operation, $params)) {
                    return true;
                }
            }
        }
        return false;

    }
}

?>
