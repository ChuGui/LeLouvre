var stripe = Stripe('pk_test_lvefCuTVu1SQoYPdJO57yFYv');
var $form = $('#payment_form');
var elements = stripe.elements();

$form.submit(function (e)
{
    e.preventDefault();
})