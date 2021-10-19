<?php

namespace Addi\Payment\Plugin\App\Request;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\CsrfValidator as MagentoCsrfValidator;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class CsrfValidator
{
    /**
     * @var FormKeyValidator
     */
    protected $_formKeyValidator;

    /**
     * @var RedirectFactory
     */
    protected $_redirectFactory;

    /**
     * @var AppState
     */
    protected $_appState;

    /**
     * @param FormKeyValidator $formKeyValidator
     * @param RedirectFactory $redirectFactory
     * @param AppState $appState
     */
    public function __construct(
        FormKeyValidator $formKeyValidator,
        RedirectFactory $redirectFactory,
        AppState $appState
    ) {
        $this->_formKeyValidator = $formKeyValidator;
        $this->_redirectFactory = $redirectFactory;
        $this->_appState = $appState;
    }


    /**
     * Validate given request.
     *
     * @param HttpRequest $request
     * @param ActionInterface $action
     *
     * @return bool
     */
    protected function validateRequest(
        HttpRequest $request,
        ActionInterface $action
    ): bool {
        $valid = null;
        if ($action instanceof CsrfAwareActionInterface) {
            $valid = $action->validateForCsrf($request);
        }

        if ($valid === null) {
            $valid = $request->isPost();
        }

        return $valid;
    }

    /**
     * Create exception for when incoming request failed validation.
     *
     * @param HttpRequest $request
     * @param ActionInterface $action
     *
     * @throws InvalidRequestException
     */
    protected function createException(
        HttpRequest $request,
        ActionInterface $action
    ) {
        $exception = null;
        if ($action instanceof CsrfAwareActionInterface) {
            $exception = $action->createCsrfValidationException($request);
        }

        if (!$exception) {
            $response = $this->_redirectFactory->create()
                ->setRefererOrBaseUrl()
                ->setHttpResponseCode(302);
            $messages = array(
                new Phrase('Invalid Form Key. Please refresh the page.'),
            );
            $exception = new InvalidRequestException($response, $messages);
        }

        throw $exception;
    }

    /**
     * @param MagentoCsrfValidator $validator
     * @param callable $proceed
     * @param RequestInterface $request
     * @param ActionInterface $action
     * @return void
     *@throws InvalidRequestException
     */
    // @codingStandardsIgnoreStart
    public function aroundValidate(
        MagentoCsrfValidator $validator,
        callable $proceed,
        RequestInterface $request,
        ActionInterface $action
    ) {
    // @codingStandardsIgnoreEnd
        if ($action instanceof \Addi\Payment\Controller\Callback\Index ) {
            try {
                $areaCode = $this->_appState->getAreaCode();
            } catch (LocalizedException $exception) {
                $areaCode = null;
            }

            if ($request instanceof HttpRequest
                && in_array(
                    $areaCode,
                    array(Area::AREA_FRONTEND, Area::AREA_ADMINHTML),
                    true
                )
            ) {
                $valid = $this->validateRequest($request, $action);
                if (!$valid) {
                   $this->createException($request, $action);
                }
            }
        } else {
            $proceed($request, $action);
        }
    }
}
