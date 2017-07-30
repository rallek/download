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
     * @param array            $options Additional options
     *
     * @return MenuItem The assembled menu
     */
    public function menu(FactoryInterface $factory, array $options)
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
        $menu->setChildrenAttribute('class', 'list-inline');

        $currentUserId = $currentUserApi->isLoggedIn() ? $currentUserApi->get('uid') : UsersConstant::USER_ID_ANONYMOUS;
        if ($entity instanceof FileEntity) {
            $component = 'RKDownLoadModule:File:';
            $instance = $entity->getKey() . '::';
            $routePrefix = 'rkdownloadmodule_file_';
            $isOwner = $currentUserId > 0 && null !== $entity->getCreatedBy() && $currentUserId == $entity->getCreatedBy()->getUid();
        
            if ($routeArea == 'admin') {
                $menu->addChild($this->__('Preview'), [
                    'route' => $routePrefix . 'display',
                    'routeParameters' => $entity->createUrlArgs()
                ])->setAttribute('icon', 'fa fa-search-plus');
                $menu[$this->__('Preview')]->setLinkAttribute('target', '_blank');
                $menu[$this->__('Preview')]->setLinkAttribute('title', $this->__('Open preview page'));
            }
            if ($context != 'display') {
                $menu->addChild($this->__('Details'), [
                    'route' => $routePrefix . $routeArea . 'display',
                    'routeParameters' => $entity->createUrlArgs()
                ])->setAttribute('icon', 'fa fa-eye');
                $menu[$this->__('Details')]->setLinkAttribute('title', str_replace('"', '', $entityDisplayHelper->getFormattedTitle($entity)));
            }
            if ($permissionApi->hasPermission($component, $instance, ACCESS_EDIT)) {
                // only allow editing for the owner or people with higher permissions
                if ($isOwner || $permissionApi->hasPermission($component, $instance, ACCESS_ADD)) {
                    $menu->addChild($this->__('Edit'), [
                        'route' => $routePrefix . $routeArea . 'edit',
                        'routeParameters' => $entity->createUrlArgs()
                    ])->setAttribute('icon', 'fa fa-pencil-square-o');
                    $menu[$this->__('Edit')]->setLinkAttribute('title', $this->__('Edit this file'));
                    $menu->addChild($this->__('Reuse'), [
                        'route' => $routePrefix . $routeArea . 'edit',
                        'routeParameters' => ['astemplate' => $entity->getKey()]
                    ])->setAttribute('icon', 'fa fa-files-o');
                    $menu[$this->__('Reuse')]->setLinkAttribute('title', $this->__('Reuse for new file'));
                }
            }
            if ($permissionApi->hasPermission($component, $instance, ACCESS_DELETE)) {
                $menu->addChild($this->__('Delete'), [
                    'route' => $routePrefix . $routeArea . 'delete',
                    'routeParameters' => $entity->createUrlArgs()
                ])->setAttribute('icon', 'fa fa-trash-o');
                $menu[$this->__('Delete')]->setLinkAttribute('title', $this->__('Delete this file'));
            }
            if ($context == 'display') {
                $title = $this->__('Back to overview');
                $menu->addChild($title, [
                    'route' => $routePrefix . $routeArea . 'view'
                ])->setAttribute('icon', 'fa fa-reply');
                $menu[$title]->setLinkAttribute('title', $title);
            }
        }

        return $menu;
    }
}
