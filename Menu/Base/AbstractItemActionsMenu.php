<?php
/**
 * DownLoad.
 *
 * @copyright Ralf Koester (RK)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Ralf Koester <ralf@familie-koester.de>.
 * @link http://oldtimer-ig-osnabrueck.de
 * @link http://zikula.org
 * @version Generated by ModuleStudio (https://modulestudio.de).
 */

namespace RK\DownLoadModule\Menu\Base;

use Knp\Menu\FactoryInterface;
use Knp\Menu\MenuItem;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\UsersModule\Constant as UsersConstant;
use RK\DownLoadModule\Entity\FileEntity;

/**
 * This is the item actions menu implementation class.
 */
class AbstractItemActionsMenu implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use TranslatorTrait;

    /**
     * Sets the translator.
     *
     * @param TranslatorInterface $translator Translator service instance
     */
    public function setTranslator(/*TranslatorInterface */$translator)
    {
        $this->translator = $translator;
    }

    /**
     * Builds the menu.
     *
     * @param FactoryInterface $factory Menu factory
     * @param array            $options List of additional options
     *
     * @return MenuItem The assembled menu
     */
    public function menu(FactoryInterface $factory, array $options = [])
    {
        $menu = $factory->createItem('itemActions');
        if (!isset($options['entity']) || !isset($options['area']) || !isset($options['context'])) {
            return $menu;
        }

        $this->setTranslator($this->container->get('translator.default'));

        $entity = $options['entity'];
        $routeArea = $options['area'];
        $context = $options['context'];

        $permissionApi = $this->container->get('zikula_permissions_module.api.permission');
        $currentUserApi = $this->container->get('zikula_users_module.current_user');
        $entityDisplayHelper = $this->container->get('rk_download_module.entity_display_helper');
        $menu->setChildrenAttribute('class', 'list-inline item-actions');

        $currentUserId = $currentUserApi->isLoggedIn() ? $currentUserApi->get('uid') : UsersConstant::USER_ID_ANONYMOUS;
        if ($entity instanceof FileEntity) {
            $component = 'RKDownLoadModule:File:';
            $instance = $entity->getKey() . '::';
            $routePrefix = 'rkdownloadmodule_file_';
            $isOwner = $currentUserId > 0 && null !== $entity->getCreatedBy() && $currentUserId == $entity->getCreatedBy()->getUid();
        
            if ($routeArea == 'admin') {
                $title = $this->__('Preview', 'rkdownloadmodule');
                $menu->addChild($title, [
                    'route' => $routePrefix . 'display',
                    'routeParameters' => $entity->createUrlArgs()
                ]);
                $menu[$title]->setLinkAttribute('target', '_blank');
                $menu[$title]->setLinkAttribute('title', $this->__('Open preview page', 'rkdownloadmodule'));
                $menu[$title]->setAttribute('icon', 'fa fa-search-plus');
            }
            if ($context != 'display') {
                $title = $this->__('Details', 'rkdownloadmodule');
                $menu->addChild($title, [
                    'route' => $routePrefix . $routeArea . 'display',
                    'routeParameters' => $entity->createUrlArgs()
                ]);
                $menu[$title]->setLinkAttribute('title', str_replace('"', '', $entityDisplayHelper->getFormattedTitle($entity)));
                $menu[$title]->setAttribute('icon', 'fa fa-eye');
            }
            if ($permissionApi->hasPermission($component, $instance, ACCESS_EDIT)) {
                // only allow editing for the owner or people with higher permissions
                if ($isOwner || $permissionApi->hasPermission($component, $instance, ACCESS_ADD)) {
                    $title = $this->__('Edit', 'rkdownloadmodule');
                    $menu->addChild($title, [
                        'route' => $routePrefix . $routeArea . 'edit',
                        'routeParameters' => $entity->createUrlArgs()
                    ]);
                    $menu[$title]->setLinkAttribute('title', $this->__('Edit this file', 'rkdownloadmodule'));
                    $menu[$title]->setAttribute('icon', 'fa fa-pencil-square-o');
                    $title = $this->__('Reuse', 'rkdownloadmodule');
                    $menu->addChild($title, [
                        'route' => $routePrefix . $routeArea . 'edit',
                        'routeParameters' => ['astemplate' => $entity->getKey()]
                    ]);
                    $menu[$title]->setLinkAttribute('title', $this->__('Reuse for new file', 'rkdownloadmodule'));
                    $menu[$title]->setAttribute('icon', 'fa fa-files-o');
                }
            }
            if ($permissionApi->hasPermission($component, $instance, ACCESS_DELETE) || ($isOwner && $permissionApi->hasPermission($component, $instance, ACCESS_EDIT))) {
                $title = $this->__('Delete', 'rkdownloadmodule');
                $menu->addChild($title, [
                    'route' => $routePrefix . $routeArea . 'delete',
                    'routeParameters' => $entity->createUrlArgs()
                ]);
                $menu[$title]->setLinkAttribute('title', $this->__('Delete this file', 'rkdownloadmodule'));
                $menu[$title]->setAttribute('icon', 'fa fa-trash-o');
            }
            if ($context == 'display') {
                $title = $this->__('Files list', 'rkdownloadmodule');
                $menu->addChild($title, [
                    'route' => $routePrefix . $routeArea . 'view'
                ]);
                $menu[$title]->setLinkAttribute('title', $title);
                $menu[$title]->setAttribute('icon', 'fa fa-reply');
            }
        }

        return $menu;
    }
}
