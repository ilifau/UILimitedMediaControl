<?php
// Copyright (c) 2017 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg, GPLv3, see LICENSE

include_once('./Services/Table/classes/class.ilTable2GUI.php');

class ilUILimitedMediaControlTableGUI extends ilTable2GUI
{
	/**
	 * @var ilUILimitedMediaControlGUI $parent_obj
	 */
	protected $parent_obj;

	/**
	 * @var string $parent_cmd
	 */
	protected $parent_cmd;

	/**
	 * @var ilUILimitedMediaControlPlugin|null
	 */
	protected $plugin;


	/**
	 * ilExteStatTableGUI constructor.
	 * @param object	$a_parent_obj
	 * @param string 	$a_parent_cmd
	 */
	public function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $lng, $ilCtrl;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->parent_obj = $a_parent_obj;
		$this->parent_cmd = $a_parent_cmd;
		$this->plugin = $a_parent_obj->getPlugin();

        $this->setId('ilUILimitedMediaControl');
        $this->setPrefix('ilUILimitedMediaControl');

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setFormName('test_overview');
        $this->setTitle($this->plugin->txt('adapted_media_limits'));
        $this->setStyle('table', 'fullwidth');
        $this->addColumn($this->lng->txt("login"));
        $this->addColumn($this->lng->txt("name"));
        $this->addColumn($this->plugin->txt("question_medium"));
        $this->addColumn($this->plugin->txt('limit'));
        $this->addColumn($this->lng->txt('actions'));

        $this->setRowTemplate("tpl.il_ui_limited_media_control_row.html", $this->plugin->getDirectory());
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->disable('sort');
        $this->enable('header');
        $this->disable('select_all');

        $this->setEnableNumInfo(false);
        $this->setExternalSegmentation(true);
	}

    /**
     * Prepare the data to be shown
     * @param ilObjTest $testObj
     * @param ilTestParticipantData $pdataObj
     */
	public function prepareData($testObj, $pdataObj)
    {
        require_once('Modules/TestQuestionPool/classes/class.assQuestion.php');
        require_once('Services/MediaObjects/classes/class.ilObjMediaObject.php');

        $rows = array();
        foreach ($this->plugin->getTestLimits($testObj->getId()) as $limit)
        {
            $row = array();
            $row['limit_obj'] = $limit;

            if ($limit->getUserId() == 0)
            {
                $row['login'] = '';
                $row['name'] = $this->plugin->txt('all_participants');
            }
            else
            {
                $active_id = $pdataObj->getActiveIdByUserId($limit->getUserId());
                $udata = $pdataObj->getUserDataByActiveId($active_id);
                $row['login'] = $udata['login'];
                $row['name'] = $this->parent_obj->formatParticipantName($active_id, false);
            }

            if ($limit->getPageId() == 0 || $limit->getMobId() == 0)
            {
                $row['medium'] = $this->plugin->txt('all_media');
            }
            else
            {
                $row['question'] = $this->parent_obj->formatQuestionMediumTitle(
                    assQuestion::_getTitle($limit->getPageId()),
                    ilObjMediaObject::_lookupTitle($limit->getMobId())
                );
            }
            $row['limit'] = $limit->getLimit();
            $rows[] = $row;
        }

        $this->setData($rows);
    }

    protected function fillRow($a_set)
    {
        /** @var ilLimitedMediaPlayerLimits $limit */
        $limit = $a_set['limit'];

        // prepare action menu
        include_once './Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
        $list = new ilAdvancedSelectionListGUI();
        $list->setSelectionHeaderClass('small');
        $list->setItemLinkClass('small');
        $list->setId('actl_'. rand(0, 999999));
        $list->setListTitle($this->lng->txt('actions'));

        $this->ctrl->setParameter($this->parent_obj, 'user_id', $limit->getUserId());
        $this->ctrl->setParameter($this->parent_obj, 'page_mob_id', $limit->getPageId().'_' . $limit->getMobId());

        $list->addItem($this->lng->txt('edit'), $this->ctrl->getLinkTarget($this->parent_obj,'editLimit'));
        $list->addItem($this->lng->txt('delete'), $this->ctrl->getLinkTarget($this->parent_obj,'deleteLimit'));

        $this->tpl->setVariable('LOGIN', $a_set['login']);
        $this->tpl->setVariable('NAME', $a_set['name']);
        $this->tpl->setVariable('MEDIUM', $a_set['medium']);
        $this->tpl->setVariable('LIMIT', $a_set['limit']);
        $this->tpl->setVariable('ACTIONS', $list->getHTML());
    }
}