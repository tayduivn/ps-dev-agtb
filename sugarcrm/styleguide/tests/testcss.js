var cssTestRunner = function ( )
{
  function test_css( elm, prop, val )
  {
    //console.log(prop+': '+$(elm).css(prop));
    test( 
      function(){ 
        assert_equals( 
          $(elm).css(prop), val 
        ) 
      }
      , elm +': ' + prop +' equals '+ val 
    );
  }

  function test_css_color( elm, prop, val )
  {
    var clr = new RGBColor( $(elm).css(prop) );

    test( 
      function(){ 
        assert_equals(
          clr.toHex().toUpperCase(), val
        ) 
      }
      , elm +': ' + prop +' equals '+ val
    );
  }

  function runSuite( data )
  {
    var i=0,l=data.length,t = {};
    for ( i=0; i<l; i+=1  )
    {
      t=data[i];
      cssTestRunner.go( t.selector, t.tests );
    }
  }

  return {
    load: function ()
    {
      var arrPath = window.location.href.split('?')[0].split('.')[0].split('/')
        , suite = arrPath.pop()
        , appl = arrPath.pop()
        , file = '/styleguide/tests/' + appl + '/' + suite + '.json';

      $.ajax({
        async:    false,
        cache:    false,
        dataType: 'json',
        url:      file,
        success:  function(data) {
          runSuite(data);
        },
        failure:  function() {
          console.log('Failed to load file: ' + file);
        }
      });
    }
  , go: function ( elm, tests )
    {
      var i = 0, l = tests.length, t = {};

      for ( i=0; i<l; i+=1 )
      {
        t = tests[i];
        if (t[0].indexOf('color')!==-1)
        {
          test_css_color( elm, t[0], t[1] );
        }
        else
        {
          test_css( elm, t[0], t[1] );
        }
      }
    }
  };
}();

$(document).ready( function(){
  if ( window.location.href.indexOf('test=1')!==-1)
  {
    $('body').append('<div id="log"></div>');
    cssTestRunner.load();
  }
} );
