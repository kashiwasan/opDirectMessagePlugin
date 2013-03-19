<?php

$results = array();
while ($row = $stmt->fetch(Doctrine_Core::FETCH_ASSOC))
{   
  $results[] = array(
    'member' => op_api_member(Doctrine::getTable('Member')->find($row['member_id'])),
//       'id' => $row['member_id'],
//       'name' => $row['name'],
//    ),
    'subject' => $row['subject'],
    'body' => $row['body'],
    'is_send' => $row['is_send'],
    'is_read' => $row['is_read'],
    'created_at' => $row['created_at'],
  );
}

return $results;
