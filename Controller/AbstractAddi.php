<?php


namespace Addi\Payment\Controller;


use Exception;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;

abstract class AbstractAddi extends Action
{

    /**
     * @var OrderFactory
     */
    public $orderFactory;
    /**
     * @var UrlHelper
     */
    protected $_urlHelper;

    /**
     * Index constructor.
     * @param UrlHelper $urlHelper
     * @param OrderFactory $orderFactory
     * @param Context $context
     */
    public function __construct(
        UrlHelper $urlHelper,
        OrderFactory $orderFactory,
        Context $context
    ) {
        parent::__construct($context);
        $this->orderFactory = $orderFactory;
        $this->_urlHelper = $urlHelper;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return ResponseInterface|Redirect
     * @throws Exception
     */
    public function reOrder($_order)
    {
        $this->messageManager->addWarningMessage(__('The payment process with Addi was not completed correctly.'));

        /** @var Order $_order */
        if ($_order->getId() == null && $_order->getId() < 1 ) {
            $this->_redirect("/");
        }

        if ($_order->canCancel()) {
            $_order->cancel();
            $_order->save();
        }

        $data = array();
        $data[ActionInterface::PARAM_NAME_URL_ENCODED] = $this->_urlHelper->getEncodedUrl();
        return $this->addToCart($_order);
    }

    /**
     * @param Order $order
     * @return string
     */
    protected function getReorderUrl($order)
    {
        return 'sales/order/reorder/order_id/' . $order->getId();
    }

    /**
     * @param Order $order
     * @return Redirect
     */
    protected function addToCart($order)
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        /** @var Cart $cart */
        $cart = $this->_objectManager->get(Cart::class);
        $items = $order->getItemsCollection();
        foreach ($items as $item) {
            try {
                $cart->addOrderItem($item);
            } catch (LocalizedException $e) {
                if ($this->_objectManager->get(Session::class)->getUseNotice(true)) {
                    $this->messageManager->addNoticeMessage($e->getMessage());
                } else {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }

                return $resultRedirect->setPath('*/*/history');
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('We can\'t add this item to your shopping cart right now.')
                );
                return $resultRedirect->setPath('checkout/cart');
            }
        }

        $cart->save();
        return $resultRedirect->setPath('checkout/cart');
    }
}
