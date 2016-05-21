<?php
function validateUserInBookhelperGroup($user){
  $userGroups = $user->getGroups();
  global $wgBookhelperGroup;
  if (! in_array($wgBookhelperGroup, $userGroups)){
    //die("You are not allowed to view this page");
    throw new PermissionsError(); // 'Missing Permissions' );
  }
}
