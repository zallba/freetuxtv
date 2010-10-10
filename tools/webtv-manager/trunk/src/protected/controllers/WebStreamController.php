<?php

class WebStreamController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';

	/**
	 * @var CActiveRecord the currently loaded data model instance.
	 */
	private $_model;

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index','view'),
				'users'=>array('*'),
			),
			array('allow',
				'actions'=>array('send'),
				'roles'=>array('sendWebStream'),
			),
			array('allow',
				'actions'=>array('update'),
				'roles'=>array('editWebStream'),
			),
			array('allow',
				'actions'=>array('changestatus'),
				'roles'=>array('changeStatusWebStream'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Displays a particular model.
	 */
	public function actionView()
	{
		$this->render('view',array(
			'model'=>$this->loadModel(),
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionUpdate()
	{
		$model=$this->loadModel();

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['WebStream']))
		{
			$model->attributes=$_POST['WebStream'];
			if($model->save()){
				$this->redirect(array('view','id'=>$model->Id));
			}
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionChangeStatus()
	{
		$model=$this->loadModel();

		if(isset($_POST['WebStream']))
		{
			$model->attributes=$_POST['WebStream'];
			if($model->save()){
				$this->redirect(array('view','id'=>$model->Id));
			}
		}

		$this->render('changestatus',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 */
	public function actionDelete()
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadModel()->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
				$this->redirect(array('index'));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$modelSearchForm = new WebStreamSearchForm;

		// collect user input data
		if(isset($_GET['WebStreamSearchForm']))
		{
			$modelSearchForm->attributes=$_GET['WebStreamSearchForm'];
		}
		
		$conditions = "";
		$params = array();
		$playlist_params = array();
		if(isset($modelSearchForm->Name)){
			$conditions = "Name LIKE :WebStreamName";
			$params[':WebStreamName'] = '%'.$modelSearchForm->Name.'%';
			if($modelSearchForm->Name != ""){
				$playlist_params["name"] = $modelSearchForm->Name;
			}
		}
		if(isset($modelSearchForm->Type)){
			if($modelSearchForm->Type != ""){
				if($conditions != ""){
					$conditions .= " AND ";
				}
				$conditions .= " TypeStream=:WebStreamType";
				$params[':WebStreamType'] = $modelSearchForm->Type;
				$playlist_params["type"] = $modelSearchForm->Type;
			}
		}
		if(isset($modelSearchForm->Status)){
			if($modelSearchForm->Status != ""){
				if($conditions != ""){
					$conditions .= " AND ";
				}
				$conditions .= " StreamStatusCode=:WebStreamStatus";
				$params[':WebStreamStatus'] = $modelSearchForm->Status;
				$playlist_params["status"] = $modelSearchForm->Status;
			}
		}
		if(isset($modelSearchForm->Language)){
			if($modelSearchForm->Language != ""){
				if($conditions != ""){
					$conditions .= " AND ";
				}
				$conditions .= " LangCode=:WebStreamLang";
				$params[':WebStreamLang'] = $modelSearchForm->Language;
				$playlist_params["lng"] = $modelSearchForm->Language;
			}
		}

		$dataProvider=new CActiveDataProvider('WebStream',array(
			'criteria'=>array(
				'condition'=>$conditions,
				'params'=>$params,
				'order'=>'Name',
			),
			'pagination'=>array(
				'pageSize'=>20,
			),
		));

		$this->render('index',array(
			'modelSearchForm'=>$modelSearchForm,
			'dataProvider'=>$dataProvider,
			'playlist_params'=>$playlist_params,
		));
	}

	/**
	 * Send a new URL.
	 */
	public function actionSend()
	{
		$model=new WebStream;

		if(isset($_POST['WebStream']))
		{
			$model->attributes=$_POST['WebStream'];
			if($model->LangCode==""){
				$model->LangCode=null;
			}
			if($model->save()){
				$this->redirect(array('view','id'=>$model->Id));
			}
		}else{
			$model->Url = $_GET['WebStreamUrl'];
		}

		$conditions = "";
		$params = array();
		$params[':WebStreamUrl'] = $model->Url;
		$conditions = "Url=:WebStreamUrl";

		$dataProvider=new CActiveDataProvider('WebStream',array(
			'criteria'=>array(
				'condition'=>$conditions,
				'params'=>$params,
				'order'=>'Name DESC',
			),
			'pagination'=>array(
				'pageSize'=>20,
			),
		));

		$this->render('send',array(
			'dataProvider'=>$dataProvider,
			'model'=>$model
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new WebStream('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['WebStream']))
			$model->attributes=$_GET['WebStream'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 */
	public function loadModel()
	{
		if($this->_model===null)
		{
			if(isset($_GET['id']))
				$this->_model=WebStream::model()->findbyPk($_GET['id']);
			if($this->_model===null)
				throw new CHttpException(404,'The requested page does not exist.');
		}
		return $this->_model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='web-stream-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
