$(function () {
    $(".knob").knob({
    	'format' : function (value) {
     	return value + '%';
  	}});

  	 $(".knob-int").knob({
    	'format' : function (value) {
     	return value;
  	}});
});