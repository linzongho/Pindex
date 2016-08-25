<?php

class SiteController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		// renders the view file 'protected/views/site/index.php'
		// using the default layout 'protected/views/layouts/main.php'

        //DAO
        $sql = 'select 
u.uid,u.name as username,u.mail,r.name as rolename
 from {{users}} u inner join {{users_roles}} ur on ur.uid = u.uid
inner join {{role}} r on r.rid = ur.rid';
        $dao = Yii::app()->db;
        $dao->active=true;
        $command=$dao->createCommand($sql);
        $rows=$command->queryAll();
        \Pindex\println($rows);

        $rows = $dao->createCommand()
            ->select('u.uid,u.name as username,u.mail,r.name as rolename')
            ->from('{{users}} u')
            ->join('{{users_roles}} as ur','ur.uid = u.uid')
            ->join('{{role}} r','r.rid = ur.rid')
            ->queryAll();
        \Pindex\println($rows);

        //AR
        $criteria = new CDbCriteria(array(
            'select'    => 't.uid,t.name as username,t.name,t.mail,r.name as rolename',
            'join'      => 'inner join {{users_roles}} as ur on ur.uid = t.uid
                            inner join {{role}} r on r.rid = ur.rid',
            'condition' => 't.name=\'lin\'',
        ));
        $ars = UsersAR::model()->findAll($criteria);
        $list = [];
        foreach ($ars as $ar){
            $list[] = [
                'uid'   => $ar->uid,
                //'select'    => 't.uid,t.name as username,t.mail,r.name as rolename',
                // XXX 'username'   => $ar->name ==> ''
                //'select'    => 't.uid,t.name as username,t.mail,r.name as rolename',
//                'username1'   => $ar->username, //Error:Property "UsersAR.username" is not defined.
                'username2'   => $ar->name,
                //'rolename'   => $ar->rolename, // Error::Property "UsersAR.rolename" is not defined.
            ];
        }
        \Pindex\println([
            $list
        ]);


        $dao->active=false;




		$this->render('index');
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}

	/**
	 * Displays the contact page
	 */
	public function actionContact()
	{
		$model=new ContactForm;
		if(isset($_POST['ContactForm']))
		{
			$model->attributes=$_POST['ContactForm'];
			if($model->validate())
			{
				$name='=?UTF-8?B?'.base64_encode($model->name).'?=';
				$subject='=?UTF-8?B?'.base64_encode($model->subject).'?=';
				$headers="From: $name <{$model->email}>\r\n".
					"Reply-To: {$model->email}\r\n".
					"MIME-Version: 1.0\r\n".
					"Content-type: text/plain; charset=UTF-8";

				mail(Yii::app()->params['adminEmail'],$subject,$model->body,$headers);
				Yii::app()->user->setFlash('contact','Thank you for contacting us. We will respond to you as soon as possible.');
				$this->refresh();
			}
		}
		$this->render('contact',array('model'=>$model));
	}

	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
		$model=new LoginForm;

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login())
				$this->redirect(Yii::app()->user->returnUrl);
		}
		// display the login form
		$this->render('login',array('model'=>$model));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}
}
