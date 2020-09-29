<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Markocupic\ContaoPetToMemberBundle\Controller\FrontendModule;

use Contao\Controller;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\Input;
use Contao\MemberModel;
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\Template;
use Haste\Form\Form;
use Haste\Util\Url;
use Markocupic\ContaoFormMultirowTextFieldBundle\Forms\FormMultirowTextField;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AssignPetToMemberModuleController.
 */
class AssignPetToMemberModuleController extends AbstractFrontendModuleController
{
    /**
     * @var ContaoFramework
     */
    private $framework;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * AssignPetToMemberModuleController constructor.
     */
    public function __construct(ContaoFramework $framework, TranslatorInterface $translator)
    {
        $this->framework = $framework;
        $this->translator = $translator;
    }

    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        /** @var MemberModel $memberModelAdapter */
        $memberModelAdapter = $this->framework->getAdapter(MemberModel::class);

        if (Input::get('id') && null !== ($objModel = $memberModelAdapter->findByPk(Input::get('id')))) {
            // Prepare serialized string from multicolumn wizard field
            // to a default array => ['dog','cat','donkey']
            $value = [];

            $arrValue = StringUtil::deserialize($objModel->pets, true);

            if (!empty($arrValue)) {
                $value = array_map(
                    static function ($row) {
                        return $row['species'];
                    },
                    $arrValue
                );
            }

            $objForm = new Form(
                'pets_form',
                'POST',
                static function ($objHaste) {
                    return Input::post('FORM_SUBMIT') === $objHaste->getFormId();
                }
            );

            $blnMandatory = false;
            $objForm->addFormField('pets', [
                'label' => $this->translator->trans('MSC.APTMMC-pets', [], 'contao_default'),
                'inputType' => 'multirowText',
                'eval' => ['mandatory' => $blnMandatory, 'multiple' => true],
                'value' => $value,
            ]);

            // Let's add a submit button
            $objForm->addFormField('submit', [
                'label' => $this->translator->trans('MSC.APTMMC-submit', [], 'contao_default'),
                'inputType' => 'submit',
            ]);

            $objForm->bindModel($objModel);

            // Save input
            if ($objForm->validate()) {
                /** @var FormMultirowTextField $objWidget */
                $objWidget = $objForm->getWidget('pets');

                $blnError = false;

                if (($blnMandatory && empty($objWidget->value)) || !\is_array($objWidget->value)) {
                    $blnError = true;
                    $objWidget->addError($this->translator->trans('ERR.APTMMC-fillInPetInput'));
                }

                if (!$blnError) {
                    // Serialize input for storing in multicolumn wizard field
                    $value = array_map(
                        static function ($el) {
                            return ['species' => $el];
                        },
                        $objWidget->value
                    );
                    $objModel->pets = serialize($value);

                    $objModel->save();
                    Controller::reload();
                }
            }
            $template->user = $objModel;
            $template->form = $objForm->generate();
        }

        // Member listing
        $objDb = Database::getInstance()->execute('SELECT * FROM tl_member');
        $template->members = $objDb->fetchAllAssoc();

        // Request
        $template->request = Url::removeQueryString(['id']);

        return $template->getResponse();
    }
}
