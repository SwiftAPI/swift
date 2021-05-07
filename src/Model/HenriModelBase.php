<?php declare(strict_types=1);
/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

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