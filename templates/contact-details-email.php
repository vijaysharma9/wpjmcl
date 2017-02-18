<p><?php printf( __( 'To contact this company email <a class="job_application_email" href="mailto:%1$s%2$s">%1$s</a>', 'wp-job-manager-company-listings' ), $email, '?subject=' . rawurlencode( $subject ) ); ?></p>

<p>
	<?php _e( 'Contact using webmail: ', 'wp-job-manager-company-listings' ); ?>

	<a href="https://mail.google.com/mail/?view=cm&fs=1&to=<?php echo $email; ?>&su=<?php echo urlencode( $subject ); ?>" target="_blank" class="job_application_email">Gmail</a> / 
	
	<a href="http://webmail.aol.com/Mail/ComposeMessage.aspx?to=<?php echo $email; ?>&subject=<?php echo urlencode( $subject ); ?>" target="_blank" class="job_application_email">AOL</a> / 
	
	<a href="http://compose.mail.yahoo.com/?to=<?php echo $email; ?>&subject=<?php echo urlencode( $subject ); ?>" target="_blank" class="job_application_email">Yahoo</a> / 
	
	<a href="http://mail.live.com/mail/EditMessageLight.aspx?n=&to=<?php echo $email; ?>&subject=<?php echo urlencode( $subject ); ?>" target="_blank" class="job_application_email">Outlook</a>
</p>