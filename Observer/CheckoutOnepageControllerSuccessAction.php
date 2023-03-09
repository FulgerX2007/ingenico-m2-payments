<?php

namespace Ingenico\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\OrderRepository;

class CheckoutOnepageControllerSuccessAction implements ObserverInterface
{
    private \Ingenico\Payment\Model\Connector $connector;

    private \Ingenico\Payment\Model\Config $cnf;

    private \Magento\Sales\Model\OrderFactory $orderFactory;

    private \Magento\Sales\Model\OrderRepository $orderRepository;

    private \Magento\Checkout\Helper\Data $checkoutHelper;

    private \Magento\Customer\Model\Session $customerSession;

    /**
     * Constructor
     *
     * @param \Ingenico\Payment\Model\Connector $connector
     * @param \Ingenico\Payment\Model\Config    $cnf
     * @param OrderFactory                      $orderFactory
     * @param OrderRepository                   $orderRepository
     * @param \Magento\Checkout\Helper\Data     $checkoutHelper
     * @param \Magento\Customer\Model\Session   $customerSession
     */
    public function __construct(
        \Ingenico\Payment\Model\Connector $connector,
        \Ingenico\Payment\Model\Config $cnf,
        OrderFactory $orderFactory,
        OrderRepository $orderRepository,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->connector = $connector;
        $this->cnf = $cnf;
        $this->orderFactory = $orderFactory;
        $this->orderRepository = $orderRepository;
        $this->checkoutHelper = $checkoutHelper;
        $this->customerSession = $customerSession;
    }

    public function execute(Observer $observer)
    {
        $order = null;
        $orderId = $this->checkoutHelper->getCheckout()->getMultishippingMainOrderId();
        if ($orderId > 0) {
            try {
                // Trigger order saving
                /** @var \Magento\Sales\Model\Order $order */
                $order = $this->orderRepository->get($orderId);
                $this->orderRepository->save($order);
            } catch (\Exception $e) {
                $this->orderRepository->save($order);
            }
        }

        // Remove Flag for Dummy shipping
        $this->customerSession->unsIsDummyShipping();

        // Remove session value
        $this->checkoutHelper->getCheckout()->unsMultishippingMainOrderId();
    }
}
