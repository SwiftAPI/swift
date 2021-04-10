<?php declare(strict_types=1);

namespace Swift\Model;

use Swift\Model\Entity\EntityManagerSingle;
use Swift\Model\Entity\EntityManagerList;

/**
 * Class HenriModelBase
 * @package Swift\Model
 *
 * @deprecated
 */
class HenriModelBase
{

	/**
	 * @var EntityManagerSingle $entityManager
	 */
	protected EntityManagerSingle $entityManagerSingle;

	/**
	 * @var EntityManagerList $entityManagerList
	 */
	protected EntityManagerList $entityManagerList;

	/**
	 * HenriModelBase constructor.
	 *
	 * @param EntityManagerSingle $entityManagerSingle
	 * @param EntityManagerList   $entityManagerList
	 */
	public function __construct(
			EntityManagerSingle $entityManagerSingle,
			EntityManagerList $entityManagerList
	) {
		$this->entityManagerSingle  = $entityManagerSingle;
		$this->entityManagerList    = $entityManagerList;
	}
}