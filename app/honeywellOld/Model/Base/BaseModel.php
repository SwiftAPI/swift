<?php declare(strict_types=1);

namespace HoneywellOld\Model\Base;

use Swift\Model\Entity\EntityManagerList;
use Swift\Model\Entity\EntityManagerSingle;
use Swift\Model\HenriModelBase;
use HoneywellOld\Helper\Authentication;

class BaseModel extends HenriModelBase {

	/**
	 * @var Authentication $helperAuthentication
	 */
	protected $helperAuthentication;

	/**
	 * BaseModel constructor.
	 *
	 * @param EntityManagerSingle $entityManagerSingle
	 * @param EntityManagerList   $entityManagerList
	 * @param Authentication      $helperAuthentication
	 */
	public function __construct(
			EntityManagerSingle $entityManagerSingle,
			EntityManagerList $entityManagerList,
			Authentication $helperAuthentication
	) {
		parent::__construct($entityManagerSingle, $entityManagerList);
		$this->helperAuthentication = $helperAuthentication;
	}


}