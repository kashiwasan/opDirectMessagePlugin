<?php

/**
 * message actions.
 *
 * @package    OpenPNE
 * @subpackage message
 * @author     Your name here
 */
class messageActions extends opJsonApiActions
{
 /**
  * Executes index action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->forward('default', 'module');
  }

  public function executeMemberList(sfWebRequest $request)
  {
    $conn = Doctrine_Core::getTable('ActivityData')->getConnection();
    $q = 'SELECT m.id, m.member_id, member.name, ms.member_id AS send_member_id, member2.name AS send_member_name, m.is_send, ms.is_read, m.subject, m.body, m.created_at '
       . 'FROM message AS m '
       . 'LEFT JOIN message_send_list AS ms ON m.id = ms.message_id '
       . 'JOIN member AS member ON m.member_id = member.id '
       . 'JOIN member AS member2 ON ms.member_id = member2.id '
       . 'WHERE ms.member_id = :send_member_id '
       . 'GROUP BY m.member_id '
       . 'ORDER BY m.created_at DESC LIMIT 0, 20';
    $params = array('send_member_id' => $this->getUser()->getMemberId());
    $this->stmt = $conn->execute($q, $params);
    
    return sfView::SUCCESS;
  }

  public function executeThread(sfWebRequest $request)
  {
    $conn = Doctrine_Core::getTable('ActivityData')->getConnection();
    $q = 'SELECT m.id, m.member_id, member.name, ms.member_id AS send_member_id, member2.name AS send_member_name, m.is_send, ms.is_read, m.subject, m.body, m.created_at '
       . 'FROM message AS m '
       . 'LEFT JOIN message_send_list AS ms ON m.id = ms.message_id '
       . 'JOIN member AS member ON m.member_id = member.id '
       . 'JOIN member AS member2 ON ms.member_id = member2.id '
       . 'WHERE ms.member_id = :send_member_id '
       . "AND m.member_id = :member_id "
       . 'ORDER BY m.created_at DESC LIMIT 0, 20';
    $params = array('send_member_id' => $this->getUser()->getMemberId(), 'member_id' => $request['member_id']);
    $this->stmt = $conn->execute($q, $params);
    $this->m = Doctrine::getTable('Member')->find($request['member_id']);
    
    return sfView::SUCCESS;
  }

  public function executePost(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Helper', 'Url', 'opUtil'));
    $this->forward400Unless(isset($request['member_id']), 'member_id parameter not specified.');
    $this->forward400Unless(is_numeric($request['member_id']), 'member_id parameter must be numeric.');
    $this->forward400Unless(isset($request['body']), 'body parameter not specified.');
    $memberId = $request['member_id'];
    $memberTo = Doctrine::getTable('Member')->find($memberId);
    $this->forward404Unless($memberTo, 'This member id does not exist.');
    $body = $request['body'];
    $subject = $request->getParameter('subject', null);
    $threadMessageId = (int)$request->getParameter('thread_message_id', 0);
    $returnMessageId = (int)$request->getParameter('return_message_id', 0);
    $messageTypeId = (int)$request->getParameter('message_type_id', 1);
    $foreignId = (int)$request->getParameter('foreign_id', 0);

    $message = new SendMessageData();
    $message->setMemberId($this->getUser()->getMember());
    $message->setSubject($subject);
    $message->setBody($body);
    $message->setIsDeleted(0);
    $message->setIsSend(true);
    $message->setThreadMessageId($threadMessageId);
    $message->setReturnMessageId($returnMessageId);
    $message->setMessageTypeId($messageTypeId);
    $message->setForeignId($foreignId);
    $message->save();

    $messageSendList = new MessageSendList();
    $messageSendList->setMemberId($memberId);
    $messageSendList->setSendMessageData($message);
    $messageSendList->save();
    $messageSendList->free();
    $message->free();

    return $this->renderJSON(array('status' => 'success'));
  }
}

