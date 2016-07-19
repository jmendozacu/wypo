<?php

$installer = $this;
$conn = $this->_conn;

$t = $this->getTable('admin_role');
$roleId = $conn->fetchOne("select role_id from {$t} where role_name='Dropship Vendor'");
if (!$roleId) {
    $conn->insert($t, array('tree_level' => 1, 'role_type' => 'G', 'role_name' => 'Dropship Vendor'));
    $roleId = $conn->lastInsertId($t);

    $rules = new Mage_Admin_Model_Rules();
    $rules->setResources(array(/*'admin/cms', 'admin/cms/page', */
        'admin/catalog', 'admin/catalog/products'));
    $rules->setRoleId($roleId)->saveRel();
}

$ut = $this->getTable('admin_user');
$vendors = $conn->fetchAll("select * from {$this->getTable('udropship_vendor')}");
$coreHlp = new Mage_Core_Helper_Data();
foreach ($vendors as $v) {
    if ($conn->fetchOne("select user_id from {$ut} where username=?", $v['email'])) {
        continue;
    }
    $conn->insert($ut, array(
        'firstname' => $v['vendor_name'],
        'lastname' => $v['vendor_attn'],
        'email' => $v['email'],
        'username' => $v['email'],
        'password' => $coreHlp->getHash($v['password'], 2),
        'created' => now(),
        'is_active' => 1,
        'udropship_vendor' => $v['vendor_id'],
    ));
    $userId = $conn->lastInsertId($ut);
    $conn->insert($t, array(
        'parent_id' => $roleId,
        'tree_level' => 2,
        'role_type' => 'U',
        'user_id' => $userId,
        'role_name' => $v['vendor_name'],
    ));
}