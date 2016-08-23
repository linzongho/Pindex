<?php
/*
* @link http://www.kalcaddle.com/
* @author warlee | e-mail:kalcaddle@qq.com
* @copyright warlee 2014.(Shanghai)Co.,Ltd
* @license http://kalcaddle.com/tools/licenses/license.txt
*/


/**
* 数据的缓存存储类；key=>value 模式；value可以是任意类型数据。
* 完整流程测试；读取最低5000次/s  含有写的1000次/s
* add   添加单条数据；已存在则返回false
* reset 重置所有数据；不传参数代表清空数据
* get:  获取数据；获取全部；获取指定key数据；获取指定多个key的数据;查找方式获取多条数据
*     1. get();
*     2. get("demo")
*     3. get(array('demo','admin'))
*     4. get('group','','root')
* update: 更新数据；更新指定key数据；获取指定多个key的数据; 查找方式更新多条数据
*     1. update("demo",array('name'=>'ddd',...))
*     2. update(array('demo','admin'),array(array('name'...),array('name'...)))
*     3. update('group','system','root')
*
* replace_update($key_old,$key_new,$value_new)替换方式更新；满足key更新的需求
*
* delete:  获取数据；获取全部；获取指定key数据；获取指定多个key的数据;查找方式获取多条数据
*     1. delete("demo")
*     2. delete(array('demo','admin'))
*     3. delete('group','','root')
*     例如:====================================
*     ['sss':['name':'sss','group':'root'],'bbb':['name':'bbb','group':'root']
*     ,'ccc':['name':'ccc','group':'system'],'ddd':['name':'ddd','group':'root']
*     查找方式删除  delete('group','','root');
*     查找方式更新  update('group','system','root');
*     查找方式获取  get('group','','root');
*/
class fileCache extends \Pindex\Library\FileCache {}