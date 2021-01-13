<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap4\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

$this->title = Yii::t('app', 'Login');
// $this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <?php $form = ActiveForm::begin([
        'id' => 'login-form',
        'options' => [
            'class' => 'form-signin'
        ]
    ]); ?>
        <h1><?= Html::encode($this->title) ?></h1>
        <?= $form->field($model, 'username', [
            'template' => '<div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-sm fa-fw fa-user"></i></span></div>{input}</div>{error}',
            'inputOptions' => [
                'placeholder' => $model->getAttributeLabel('username'),
            ],
        ])->label(false);
        ?>
        <?= $form->field($model, 'password', [
           'template' => '<div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-sm fa-fw fa-lock"></i></span></div>{input}</div>{error}',
           'inputOptions' => [
                'placeholder' => $model->getAttributeLabel('password'),
            ],
        ])->passwordInput()->label(false);
        ?>

        <?php if ($model->scenario == 'withCaptcha'): ?>
            <?= $form->field($model, 'verifyCode', [
           'inputOptions' => [
                'placeholder' => $model->getAttributeLabel('verifyCode'),
            ],
        ])->widget(\yii\captcha\Captcha::class,[
            'template' => '<div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-sm fa-fw fa-check"></i></span></div>{input}</div>
            <div class="list-group text-center" style="margin-top: 1rem;"><div class="list-group-item">{image}<span class="text-secondary d-none d-sm-inline">点击图片换图</span></div></div>',
        ])->label(false);
    ?>
        <?php endif; ?>
        <?= $form->field($model, 'rememberMe')->checkbox() ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Login'), ['class' => 'btn btn-block btn-primary btn-success', 'name' => 'login-button']) ?>
            <div class="btn-group btn-block">
            <?= Html::a('忘记密码', ['site/request-password-reset'], ['class' => 'btn text-secondary']) ?>
            <?= Html::a('注册新帐号', ['site/signup'], ['class' => 'btn text-secondary']) ?>
        </div>

    <?php ActiveForm::end(); ?>

</div>
