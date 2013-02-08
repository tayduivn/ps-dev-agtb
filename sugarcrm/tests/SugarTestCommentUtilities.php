<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

require_once 'modules/ActivityStream/Comments/Comment.php';

class SugarTestCommentUtilities
{
    private static $_createdComments = array();

    public static function createUnsavedComment(Activity $a = null, $new_id = '')
    {
        $time = mt_rand();
        $data = array('value' => 'SugarComment' . $time);
        $comment = new Comment();
        $comment->data = $data;
        if ($a && $a->id) {
            $comment->parent_id = $a->id;
        }
        if (!empty($new_id)) {
            $comment->new_with_id = true;
            $comment->id = $new_id;
        }
        return $comment;
    }

    public static function createComment(Activity $a = null, $new_id = '')
    {
        $comment = self::createUnsavedComment($a, $new_id);
        if ($comment) {
            $comment->save();
            $GLOBALS['db']->commit();
            self::$_createdComments[] = $comment;
        }
        return $comment;
    }

    public static function setCreatedComment($comment_ids)
    {
        foreach ($comment_ids as $comment_id) {
            $comment = new Comment();
            $comment->id = $comment_id;
            self::$_createdComments[] = $comment;
        }
    }

    public static function removeAllCreatedComments()
    {
        $comment_ids = self::getCreatedCommentIds();
        $GLOBALS['db']->query('DELETE FROM comments WHERE id IN (\'' . implode("', '", $comment_ids) . '\')');
    }

    public static function getCreatedCommentIds()
    {
        $comment_ids = array();
        foreach (self::$_createdComments as $comment) {
            $comment_ids[] = $comment->id;
        }
        return $comment_ids;
    }
}
