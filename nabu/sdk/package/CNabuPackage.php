<?php

/*  Copyright 2009-2011 Rafael Gutierrez Martinez
 *  Copyright 2012-2013 Welma WEB MKT LABS, S.L.
 *  Copyright 2014-2016 Where Ideas Simply Come True, S.L.
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace nabu\sdk\package;
use nabu\core\CNabuObject;
use nabu\core\exceptions\ENabuCoreException;
use nabu\core\exceptions\ENabuSecurityException;
use nabu\data\customer\CNabuCustomer;
use nabu\data\customer\traits\TNabuCustomerChild;
use nabu\data\lang\CNabuLanguageList;
use nabu\data\security\CNabuRoleList;
use nabu\data\site\CNabuSite;
use nabu\data\site\CNabuSiteList;
use nabu\sdk\builders\xml\CNabuXMLBuilder;
use nabu\xml\lang\CNabuXMLLanguageList;
use nabu\xml\security\CNabuXMLRoleList;
use nabu\xml\site\CNabuXMLSiteList;

/**
 * This class manages a package distribution of nabu-3.
 * @author Rafael Gutierrez <rgutierrez@nabu-3.com>
 * @since 3.0.8 Surface
 * @version 3.0.8 Surface
 */
class CNabuPackage extends CNabuObject
{
    use TNabuCustomerChild;

    /** @var CNabuLanguageList $nb_language_list List of Languages to be included in this package. */
    private $nb_language_list = null;
    /** @var CNabuRoleList $nb_role_list List of Roles to be included in this package. */
    private $nb_role_list = null;
    /** @var CNabuSiteList $nb_site_list List of Sites to be included in this package. */
    private $nb_site_list = null;

    public function __construct(CNabuCustomer $nb_customer)
    {
        parent::__construct();

        $this->setCustomer($nb_customer);
        $this->nb_language_list = new CNabuLanguageList();
        $this->nb_role_list = new CNabuRoleList($nb_customer);
        $this->nb_site_list = new CNabuSiteList($nb_customer);
    }

    /**
     * Add a list of Sites and their dependencies to this package instance.
     * @param array $list Mixed list of CNabuList instances and/or Site Ids.
     * @return int Returns the number of Sites added.
     */
    public function addSites(array $list) : int
    {
        $count = $this->getObjectsCount();
        $nb_customer = $this->getCustomer();

        foreach ($list as $item) {
            if (!($item instanceof CNabuSite) &&
                is_numeric($nb_site_id = nb_getMixedValue($item, NABU_SITE_FIELD_ID))
            ) {
                $nb_site = $nb_customer->getSite($nb_site_id);
                if (!$nb_site instanceof CNabuSite) {
                    throw new ENabuCoreException(ENabuCoreException::ERROR_SITE_NOT_FOUND);
                }
            } elseif ($item instanceof CNabuSite) {
                $nb_site = $item;
                if (!$nb_site->validateCustomer($nb_customer)) {
                    throw new ENabuSecurityException(ENabuSecurityException::ERROR_CUSTOMER_NOT_OWNER);
                }
            } else {
                throw new ENabuCoreException(ENabuCoreException::ERROR_SITE_NOT_FOUND);
            }

            $nb_site->refresh(true, true);
            $this->nb_site_list->addItem($nb_site);
            $this->nb_language_list->merge($nb_site->getLanguages(true));
            $this->nb_role_list->merge($nb_site->getRoles(true));
        }

        return $this->getObjectsCount() - $count;
    }

    public function getObjectsCount()
    {
        return $this->nb_language_list->getSize() +
               $this->nb_role_list->getSize() +
               $this->nb_site_list->getSize()
        ;
    }

    public function export(string $filename)
    {
        if ($this->getObjectsCount() > 0) {
            $file = new CNabuXMLBuilder();
            if ($this->nb_language_list->getSize() > 0) {
                $file->addFragment(new CNabuXMLLanguageList($this->nb_language_list));
            }
            if ($this->nb_role_list->getSize() > 0) {
                $file->addFragment(new CNabuXMLRoleList($this->nb_role_list));
            }
            if ($this->nb_site_list->getSize() > 0) {
                $file->addFragment(new CNabuXMLSiteList($this->nb_site_list));
            }
            $file->create();
            $file->exportToFile($filename);
        }
    }
}