// http://stackoverflow.com/questions/9143971/using-twitter-bootstrap-2-0-how-to-make-equal-column-heights
$('.well').css({'height': $('.well').outerHeight()});


// File upload button
// http://viget.com/inspire/custom-file-inputs-with-a-bit-of-jquery/
// https://gist.github.com/645816
$(document).ready(function() {
  $('.file-wrapper input[type=file]').bind('change focus click', function() {
    var $this = $(this),
        $val = $this.val(),
        valArray = $val.split('\\'),
        newVal = valArray[valArray.length-1],
        $button = $this.siblings('.btn');
    if(newVal !== '') 
    {
      $button.text('Converting');
      $button.closest('form').submit();
    }
  });
});
