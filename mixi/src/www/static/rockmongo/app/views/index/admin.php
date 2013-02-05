 <frameset rows="21,*"> 
	<frame src="<?php echo $topUrl;?>" name="top" frameborder="no" marginheight="0" scrolling="no"> 
	<frameset cols="15%,*"> 
		<frame src="<?php echo $leftUrl;?>" name="left" frameborder="no" scrolling="auto" marginheight="0"> 
		<frame src="<?php echo $rightUrl;?>" name="right" frameborder="no" marginheight="0"> 
	</frameset> 
	<noframes> 
		<h2>frame alert</h2> 
		<p>this document is designed to be viewed using the frames feature.
		if you see this message, you are using a non-frame-capable web client.</p> 
	</noframes> 
</frameset>