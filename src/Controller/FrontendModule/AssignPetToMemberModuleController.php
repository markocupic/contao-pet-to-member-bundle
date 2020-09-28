<?php

/**
 * This file is part of a markocupic Contao Bundle.
 *
 * (c) Marko Cupic 2020 <m.cupic@gmx.ch>
 * @author     Marko Cupic
 * @package    Formulartest
 * @license    MIT
 * @see        https://github.com/markocupic/contao-pet-to-member-bundle
 *
 */

declare(strict_types=1);

namespace Markocupic\ContaoPetToMemberBundle\Controller\FrontendModule;

use Contao\Controller;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\Database;
use Contao\Environment;
use Contao\Input;
use Contao\MemberModel;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\Template;
use Haste\Form\Form;
use Haste\Util\Url;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;


/**
 * Class AssignPetToMemberModuleController
 *
 * @package Markocupic\ContaoPetToMemberBundle\Controller\FrontendModule
 */
class AssignPetToMemberModuleController extends AbstractFrontendModuleController
{

    /** @var TranslatorInterface */
    private $translator;

    /**
     * AssignPetToMemberModuleController constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param Template $template
     * @param ModuleModel $model
     * @param Request $request
     * @return null|Response
     */
    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {


        if (Input::get('id') && null !== ($objModel = MemberModel::findByPk(Input::get('id'))))
        {

            // Prepare serialized string from multicolumn wizard field
            // to a default array => ['dog','cat','donkey']
            $value = [];

            $arrValue = StringUtil::deserialize($objModel->pets, true);
            if (!empty($arrValue))
            {
                $value = array_map(function ($row) {
                    return $row['species'];
                }, $arrValue);
            }

            $objForm = new Form('pets_form', 'POST', function ($objHaste) {
                return Input::post('FORM_SUBMIT') === $objHaste->getFormId();
            });

            $blnMandatory = false;
            $objForm->addFormField('pets', [
                'label'     => $this->translator->trans('MSC.APTMMC-pets', [], 'contao_default'),
                'inputType' => 'multirowText',
                'eval'      => ['mandatory' => $blnMandatory, 'multiple' => true],
                'value'     => $value,
            ]);

            // Let's add a submit button
            $objForm->addFormField('submit', [
                'label'     => $this->translator->trans('MSC.APTMMC-submit', [], 'contao_default'),
                'inputType' => 'submit',
            ]);

            $objForm->bindModel($objModel);

            // Save input
            if ($objForm->validate())
            {
                $objWidget = $objForm->getWidget('pets');
                if ($blnMandatory && empty($objWidget->value) || !is_array($objWidget->value))
                {
                    $blnError = true;
                    $objWidget->addError($this->trans('ERR.APTMMC-fillInPetInput'));
                }

                if (!$blnError)
                {
                    // Serialize input for storing in multicolumn wizard field
                    $value = array_map(function ($el) {
                        return ['species' => $el];
                    }, $objWidget->value);
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
