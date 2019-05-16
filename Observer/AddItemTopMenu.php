<?php
/**
 * Copyright © 2016 Net Gasoline. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Netgasoline\Extendnavigation\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Data\Tree\Node;

class AddItemTopMenu implements ObserverInterface
{
	
		public function __construct(
		\Magento\Framework\Locale\ResolverInterface $localeResolver,
		\Magento\Catalog\Api\Data\CategoryTreeInterfaceFactory $treeFactory
            ) {
			$this->localeResolver = $localeResolver;
			$this->treeFactory = $treeFactory;
        }

    public function execute(EventObserver $observer)
    {
        $menu = $observer->getMenu();
        $tree = $menu->getTree();
		
		if (file_exists(__DIR__ . '/../ExtendedMenues/'.$this->localeResolver->getLocale().'.xml')) {
   		 $LocalMenu = simplexml_load_file(__DIR__ . '/../ExtendedMenues/'.$this->localeResolver->getLocale().'.xml');
			$NumberOfChildren = $LocalMenu->Before->MenuItem->count();
			for($i=$NumberOfChildren-1;$i>=0;$i--) {
						$nodeID = $this->randomString(6);
				        $data = [
			           	'name'      => $LocalMenu->Before->MenuItem[$i]['title'],
          				 'id'        => $nodeID,
           				 'url'       => $LocalMenu->Before->MenuItem[$i]['link'],
           				 'is_active' => false
        				];
						$node = new Node($data, 'id', $tree, $menu);
						$this->XmlTreeRecursive($LocalMenu->Before->MenuItem[$i], $node, $menu, $tree);
						$this->_prependNode($node, $menu);

			}
			 $j=0;
			 foreach ($LocalMenu->After->MenuItem as $MenuElement) {
				        $data = [
           				 'name'      => $MenuElement['title'],
          				 'id'        => $this->randomString(6),
           				 'url'       => $MenuElement['link'],
           				 'is_active' => false
        				];
						$node = new Node($data, 'id', $tree, $menu);
						$this->XmlTreeRecursive($LocalMenu->After->MenuItem[$j++], $node, $menu, $tree);
						$menu->getChildren()->add($node);
			}
		}
    }
	
	protected function XmlTreeRecursive ($XmlData, $node, $menu, $tree) {
		foreach ($XmlData->MenuItem as $MenuElement) {
				        $data = [
			           	'name'      => $MenuElement['title'],
          				 'id'        => $this->randomString(6),
           				 'url'       => $MenuElement['link'],
           				 'is_active' => false
        				];
						$NewNode = new Node($data, 'id', $tree, $menu);
						$node->getChildren()->add($NewNode);
						$this->XmlTreeRecursive($MenuElement, $NewNode, $menu, $tree);
				}
		}
	
	
	protected function _prependNode($node, $menu)
	{
    $menu->addChild($node);
    $nodeId = $node->getId();
    $readded = array();
    foreach ($menu->getChildren()->getNodes() as $n)
    {
        if ($n->getId() != $nodeId)
        {
            $readded[] = $n;
            $menu->getChildren()->delete($n);
        }
    }
    foreach($readded as $r)
    {
        $menu->getChildren()->add($r);
    }
	}
	
	protected function randomString($length = 6) {
	$str = "";
	$characters = array_merge(range('A','Z'), range('a','z'), range('0','9'));
	$max = count($characters) - 1;
	for ($i = 0; $i < $length; $i++) {
		$rand = mt_rand(0, $max);
		$str .= $characters[$rand];
	}
	return $str;
}
}