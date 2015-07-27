<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\UserBundle\Document;

use FOS\UserBundle\Doctrine\UserManager as BaseUserManager;
use Sonata\UserBundle\Model\UserManagerInterface;
use Sonata\DatagridBundle\Pager\Doctrine\Pager;
use Sonata\DatagridBundle\ProxyQuery\Doctrine\ProxyQuery;

/**
 * Class UserManager
 *
 * @package Sonata\UserBundle\Document
 *
 * @author Hugo Briand <briand@ekino.com>
 */
class UserManager extends BaseUserManager implements UserManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function findUsersBy(array $criteria = null, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function getPager(array $criteria, $page, $limit = 10, array $sort = array()) {
        $query = $this->repository
                ->createQueryBuilder('u')
                ->select('u');

        $fields = $this->objectManager->getClassMetadata($this->class)->getFieldNames();
        foreach ($sort as $field => $direction) {
            if (!in_array($field, $fields)) {
                unset($sort[$field]);
            }
        }
        if (count($sort) == 0) {
            $sort = array('username' => 'ASC');
        }
        foreach ($sort as $field => $direction) {
            $query->orderBy(sprintf('u.%s', $field), strtoupper($direction));
        }

        $parameters = array();

        if (isset($criteria['enabled'])) {
            $query->andWhere('u.enabled = :enabled');
            $parameters['enabled'] = $criteria['enabled'];
        }

        if (isset($criteria['locked'])) {
            $query->andWhere('u.locked = :locked');
            $parameters['locked'] = $criteria['locked'];
        }

        $query->setParameters($parameters);

        $pager = new Pager();
        $pager->setMaxPerPage($limit);
        $pager->setQuery(new ProxyQuery($query));
        $pager->setPage($page);
        $pager->init();

    }
}
