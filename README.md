# [Featured Image as YouTube Player](https://github.com/brasofilo/Featured-Image-as-YouTube-Player)

*Swaps the Featured Image by a YouTube player (click to load/play).*

<sup>***Default meta box with an extra field***</sup>  
![metabox](https://raw.github.com/brasofilo/Featured-Image-as-YouTube-Player/master/screenshots/meta-box.png)

<sup>***Featured image with rollover***</sup>  
![before](https://raw.github.com/brasofilo/Featured-Image-as-YouTube-Player/master/screenshots/before-click.png)

<sup>***Clean player loaded after click***</sup>  
![after](https://raw.github.com/brasofilo/Featured-Image-as-YouTube-Player/master/screenshots/before-click.png)
 
## Configuration

Configurable YouTube parameters. Comments indicate official parameters.
[Documentation](https://developers.google.com/youtube/player_parameters)

    $video_params = array(
    	// Values: 2 (default), 1, and 0
    	'autohide' => '1', 
    
    	// Values: 0 (default) or 1
    	'autoplay' => '1', 
    
    	// Values red (default) and white. White disables modestbranding(!)
    	'color' => 'white', 
    
    	// Values: 0, 1 (default), or 2
    	'controls' => '2',
    
    	// Values: 0 or 1. Variable default. Full screen
    	'fs' => '1', 
    
    	// Values: 1 (default) or 3. Video annotations. 3 = hide
    	'iv_load_policy' => '3',
    
    	// Values: 0 (default) or 1.
    	'loop' => '0', 
    
    	// Set the parameter value to 1 to prevent the YouTube logo 
    	// from displaying in the control bar. No default given.
    	'modestbranding' => '1', 
    
    	// Values: 0, 1 (default). Related videos.
    	'rel' => '0',
    
    	// Values: 0, 1 (default). Related videos.
    	'showinfo' => '1',
    
    	// Values: dark (default) and light.
    	'theme' => 'dark',            
    );

## Credits
 - Plugin skeleton from [Plugin Class Demo](https://gist.github.com/3804204), by toscho. 
 - [CSS Overlay images](http://stackoverflow.com/q/403478), by Tim K. 
 - [Extracting image attributes from Html](http://stackoverflow.com/a/10131137), by hackre.
 - [Grabbing ID from YouTube URL](http://stackoverflow.com/a/6556662), by hackre.

## Licence
Released under GPL, you can use it free of charge on your personal or commercial blog.