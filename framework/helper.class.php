<?php
/**
 * The helper class file of ZenTaoPHP.
 *
 * ZenTaoPHP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 * ZenTaoPHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with ZenTaoPHP.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright   Copyright 2009, Chunsheng Wang
 * @author      Chunsheng Wang <wwccss@gmail.com>
 * @package     ZenTaoPHP
 * @version     $Id: helper.class.php 1225 2009-07-27 13:36:15Z wwccss $
 * @link        http://www.zentao.cn
 */
/**
 * 工具类对象，存放着各种杂项的工具方法。
 *
 * @package ZenTaoPHP
 */
class helper
{
    /**
     * 为一个对象设置某一个属性，其中key可以是“father.child”的形式。
     * 
     * <code>
     * <?php
     * $lang->db->user = 'wwccss';
     * helper::setMember('lang', 'db.user', 'chunsheng.wang');
     * ?>
     * </code>
     * @param string    $objName    对象变量名。
     * @param string    $key        要设置的属性，可以是father.child的形式。
     * @param mixed     $value      要设置的值。
     * @static
     * @access public
     * @return void
     */
    static public function setMember($objName, $key, $value)
    {
        global $$objName;
        if(!is_object($$objName) or empty($key)) return false;
        $key   = str_replace('.', '->', $key);
        $value = serialize($value);
        $code  = ("\$${objName}->{$key}=unserialize(<<<EOT\n$value\nEOT\n);");
        eval($code);
    }

    /**
     * 生成某一个模块某个方法的链接。
     * 
     * 在control类中对此方法进行了封装，可以在control对象中直接调用createLink方法。
     * <code>
     * <?php
     * helper::createLink('hello', 'index', 'var1=value1&var2=value2');
     * helper::createLink('hello', 'index', array('var1' => 'value1', 'var2' => 'value2');
     * ?>
     * </code>
     * @param string    $moduleName     模块名。
     * @param string    $methodName     方法名。
     * @param mixed     $vars           要传递给method方法的各个参数，可以是数组，也可以是var1=value2&var2=value2的形式。
     * @static
     * @access public
     * @return string
     */
    static public function createLink($moduleName, $methodName = 'index', $vars = '')
    {
        global $app, $config;
        $link = $config->webRoot;

        /* 如果传递进来的vars不是数组，尝试将其解析成数组格式。*/
        if(!is_array($vars)) parse_str($vars, $vars);
        if($config->requestType == 'PATH_INFO')
        {
            $link .= "$moduleName{$config->requestFix}$methodName";
            if($config->pathType == 'full')
            {
                foreach($vars as $key => $value) $link .= "{$config->requestFix}$key{$config->requestFix}$value";
            }
            else
            {
                foreach($vars as $value) $link .= "{$config->requestFix}$value";
            }    
            $link .= '.' . $app->getViewType();
        }
        elseif($config->requestType == 'GET')
        {
            $link .= "?{$config->moduleVar}=$moduleName&{$config->methodVar}=$methodName&{$config->viewVar}=" . $app->getViewType();
            foreach($vars as $key => $value) $link .= "&$key=$value";
        }
        return $link;
    }

    /**
     * 将一个数组转成对象格式。此函数只是返回语句，需要eval。
     * 
     * <code>
     * <?php
     * $config['user'] = 'wwccss';
     * eval(helper::array2Object($config, 'configobj');
     * print_r($configobj);
     * ?>
     * </code>
     * @param array     $array          要转换的数组。
     * @param string    $objName        要转换成的对象的名字。
     * @param string    $memberPath     成员变量路径，最开始为空，从根开始。
     * @param bool      $firstRun       是否是第一次运行。
     * @static
     * @access public
     * @return void
     */
    static public function array2Object($array, $objName, $memberPath = '', $firstRun = true)
    {
        if($firstRun)
        {
            if(!is_array($array) or empty($array)) return false;
        }    
        static $code = '';
        $keys = array_keys($array);
        foreach($keys as $keyNO => $key)
        {
            $value = $array[$key];
            if(is_int($key)) $key = 'item' . $key;
            $memberID = $memberPath . '->' . $key;
            if(!is_array($value))
            {
                $value = addslashes($value);
                $code .= "\$$objName$memberID='$value';\n";
            }
            else
            {
                helper::array2object($value, $objName, $memberID, $firstRun = false);
            }
        }
        return $code;
    }

    /**
     * 包含一个文件。router.class.php和control.class.php中包含文件都通过此函数来调用，这样保证文件不会重复加载。
     * 
     * @param string    $file   要包含的文件的路径。 
     * @static
     * @access public
     * @return void
     */
    static public function import($file)
    {
        if(!file_exists($file)) return false;
        static $includedFiles = array();
        if(!in_array($file, $includedFiles))
        {
            include $file;
            $includedFiles[] = $file;
        }
    }
}
