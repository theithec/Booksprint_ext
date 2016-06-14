<?php
function validateUserInBookhelperGroup($user){
  $userGroups = $user->getGroups();
  global $wgBookhelperGroup;
  if (! in_array($wgBookhelperGroup, $userGroups)){
    throw new PermissionsError("edit");
  }
}
