<?php
namespace ls\controllers;
use \Yii;
use CFileHelper, CClientScript, CHtml;
use \elFinder, elFinderConnector;
class FilesController extends Controller
{
    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * You may override this method to do last-minute preparation for the action.
     * @param CAction $action the action to be executed.
     * @return boolean whether the action should be executed.
     */
    protected function beforeAction($action)
    {
        $result = parent::beforeAction($action);

        foreach (App()->log->routes as $route) {
            if ($route instanceof \CWebLogRoute) {
                $route->enabled = false;
            }
        }
        return $result;
    }

    /**
     * Shows the file manager.
     * @param bool|false $dialog
     * @param null $surveyId
     */
    public function actionManage($dialog = false, $context, $key)
    {
        $this->renderText($this->widget(\FileManagerWidget::class, [
            'context' => $context,
            'key' => $key
        ], true));
    }


    /**
     * The browse function used by elFinder.
     */
    public function actionBrowse($context, $key) {
        switch ($context) {
            case 'survey':
                $roots = [$this->getRootForSurvey($key)];
                break;
            case 'template':
                $roots = $this->getRootForTemplate($key);
                break;
            default:
                throw new \CHttpException(404, "Unknown context");
        }
        $finder = new elFinder([
            'debug' => true,
            'roots' => $roots,
            'mimeDetect' => 'internal',


        ]);

        $connector = new elFinderConnector($finder, false);
        $connector->run();
    }


    /**
     * @param int $surveyId
     * @return array
     * @todo Add permission check and set appropriate rights.
     */
    protected function getRootForSurvey($surveyId) {

        $relative = "/upload/surveys/$surveyId";
        $dir = Yii::getPathOfAlias('webroot') . $relative;
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        return [
            'alias' => "Survey ({$surveyId})",
            'driver' => 'LocalFileSystem',
            'path'   => $dir,
            'URL' => App()->baseUrl . $relative,
            'icon' => "{$this->getAssetsUrl()}/img/volume_icon_local.png",
            'accessControl' => 'access',
            'attributes' => [
                [
                    // hide anything else
                    'pattern' => '!^/\..*$!',
                    'hidden' => true
                ]
            ]
        ];
    }

    /**
    * @param int $surveyId
    * @return array
    * @todo Add permission check and set appropriate rights.
    */
    protected function getRootForTemplate($template) {
        $result = [];
        if (true || !\Template::isStandardTemplate($template)) {
            $dir = \Template::getTemplatePath($template);
            $url = \Template::getTemplateURL($template);
            $result[] = [
                'alias' => "Template ({$template})",
                'driver' => 'LocalFileSystem',
                'path' => $dir,
                'URL' => App()->baseUrl . $url,
                'mimeMap' => [
                    "pstpl:text/plain" => "text/html",
                    "css:text/plain" => "text/css"
                ],
                'icon' => "{$this->getAssetsUrl()}/img/volume_icon_local.png",
                'accessControl' => 'access',
                'attributes' => [
                    [
                        // hide anything else
                        'pattern' => '!^/\..*$!',
                        'hidden' => true
                    ]
                ]
            ];
        }
        return $result;
    }
    protected function getAssetsUrl() {
        $dir = __DIR__ . '/../vendor/studio-42/elfinder/';
        $url = App()->assetManager->getPublishedUrl($dir);
        return $url;
    }
    protected function getRoots()
    {

        $result = [];
        // Get accessible surveys.
        foreach (\Survey::model()->accessible()->findAll() as $survey) {
            $result[] = $this->getRootForSurvey($survey->primaryKey);
        }
//        vdd($result);
//        $result[] = [
//            'alias' => "All surveys",
//            'driver' => 'LocalFileSystem',
////            'path'   => Yii::getPathOfAlias('webroot'),
////            'URL' => '/files'
//            'path'   => Yii::getPathOfAlias('webroot') . "/upload/surveys",
//            'URL' => App()->baseUrl . "/upload/surveys",
//            'attributes' => [
//                [
//                    // hide anything else
//                    'pattern' => '!^/\..*$!',
//                    'hidden' => true
//                ]
//            ]
//        ];
//        vdd($result);
        return $result;
    }

}