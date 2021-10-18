<?php


namespace Addi\Payment\Ui\Component\Listing\Column;

use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;

class Addi extends Column
{
    protected $_orderRepository;
    protected $_searchCriteria;

<<<<<<< HEAD
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $criteria,
        array $components = array(),
        array $data = array()
    ){
=======
    public function __construct(ContextInterface $context, UiComponentFactory $uiComponentFactory, OrderRepositoryInterface $orderRepository, SearchCriteriaBuilder $criteria, array $components = [], array $data = [])
    {
>>>>>>> 708930370b8218fe39a37235b3cf07ba6e4c7cd6
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
<<<<<<< HEAD
=======

>>>>>>> 708930370b8218fe39a37235b3cf07ba6e4c7cd6
                $order  = $this->_orderRepository->get($item["entity_id"]);
                $status = $order->getData("addi_id");
                // $this->getData('name') returns the name of the column so in this case it would return export_status
                $item[$this->getData('name')] = $status;
            }
        }

        return $dataSource;
    }
}
