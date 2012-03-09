$(document).ready(function() {
  $('.alert').fadeOut(5000);

  // http://stackoverflow.com/questions/9143971/using-twitter-bootstrap-2-0-how-to-make-equal-column-heights
  $('.well').css({'height': $('.well').height()});


  // File upload button
  // http://viget.com/inspire/custom-file-inputs-with-a-bit-of-jquery/
  // https://gist.github.com/645816
  $('form').bind('change', function() {
    var form = $(this),
        input = form.find('.file-wrapper input[type=file]');
    if(input.val())
    {
      form.submit();
      form.each(function(){ this.reset(); });
    }
  });
});
