<?php if (!defined('APPLICATION')) exit();
/*
Copyright 2008, 2009 Vanilla Forums Inc.
This file is part of Garden.
Garden is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
Garden is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with Garden.  If not, see <http://www.gnu.org/licenses/>.
Contact Vanilla Forums Inc. at support [at] vanillaforums [dot] com
*/

/**
 * Renders recently active bookmarked discussions
 */
class BookmarkedModule extends Gdn_Module {
   public $Limit = 10;
   public $Help = FALSE;
   
   public function __construct() {
      parent::__construct();
      $this->_ApplicationFolder = 'vanilla';
   }
   
   public function GetData() {
      $this->Data = FALSE;
      if (Gdn::Session()->IsValid() && C('Vanilla.Modules.ShowBookmarkedModule', TRUE)) {
         $BookmarkIDs = Gdn::SQL()
            ->Select('DiscussionID')
            ->From('UserDiscussion')
            ->Where('UserID', Gdn::Session()->UserID)
            ->Where('Bookmarked', 1)
            ->Get()->ResultArray();
         $BookmarkIDs = ConsolidateArrayValuesByKey($BookmarkIDs, 'DiscussionID');

         if (count($BookmarkIDs)) {
            $DiscussionModel = new DiscussionModel();
            DiscussionModel::CategoryPermissions();

            $DiscussionModel->SQL->WhereIn('d.DiscussionID', $BookmarkIDs);
            
            $this->Data = $DiscussionModel->Get(
               0,
               $this->Limit,
               array( 'w.Bookmarked' => '1' )
            );
         } else {
            $this->Data = new Gdn_DataSet();
         }
      }
   }

   public function AssetTarget() {
      return 'Panel';
   }

   public function ToString() {
      if (!isset($this->Data))
         $this->GetData();
      
      if (is_object($this->Data) && ($this->Data->NumRows() > 0 || $this->Help))
         return parent::ToString();
      
      return '';
   }
}