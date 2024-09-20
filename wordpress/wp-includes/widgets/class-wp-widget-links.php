strator\AppData\Local\Temp\l3sy1453\Win11SDK_10.0.22621.00C0B50536C9040EC40F\Installers\Windows App Certification Kit SupportedApiList x86-x86_en-us.msi
[17e4:0019][2024-08-13T08:50:38] SHA256 verification for 'C:\Users\Administrator\AppData\Local\Temp\l3sy1453\Win11SDK_10.0.22621.00C0B50536C9040EC40F\Installers\Windows App Certification Kit SupportedApiList x86-x86_en-us.msi' succeeded. Hash: 7F56D7AE0501D1CD252D7BB2C7A88D98337A900328FBB8D7A45DA48E5612FCB7
[17e4:0019][2024-08-13T08:50:38] Checking SHA256 for path: C:\Users\Administrator\AppData\Local\Temp\l3sy1453\Win11SDK_10.0.22621.00C0B50536C9040EC40F\Installers\Windows App Certification Kit x64 (OnecoreUAP)-x86_en-us.msi
[17e4:0019][2024-08-13T08:50:38] SHA256 verification for 'C:\Users\Administrator\AppData\Local\Temp\l3sy1453\Win11SDK_10.0.22621.00C0B50536C9040EC40F\Installers\Windows App Certification Kit x64 (OnecoreUAP)-x86_en-us.msi' succeeded. Hash: 04F1B1E1344386D4261B26D55ADD689FBF67DC70E94C50C4C983E5D4F3CE2FC7
[17e4:0019][2024-08-13T08:50:38] Checking SHA256 for path: C:\Users\Administrator\AppData\Local\Temp\l3sy1453\Win11SDK_10.0.22621.00C0B50536C9040EC40F\Installers\Windows App Certification Kit x64-x86_en-us.msi
[17e4:0019][2024-08-13T08:50:38] SHA256 verification for 'C:\Users\Administrator\AppData\Local\Temp\l3sy1453\Win11SDK_10.0.22621.00C0B50536C9040EC40F\Installers\Windows App Certification Kit x64-x86_en-us.msi' succeeded. Hash: A9DE095A6B216293806592EC8AD5FF967153568F9529C87AE3DA684C2C4EB4DD
[17e4:0019][2024-08-13T08:50:38] Checking SHA256 for path: C:\Users\Administrator\AppData\Local\Temp\l3sy1453\Win11SDK_10.0.22621.00C0B50536C9040EC40F\Installers\Windows App Certification Kit x86 (OnecoreUAP)-x86_en-us.msi
[17e4:0019][2024-08-13T08:50:38] SHA256 verification for 'C:\Users\Administrator\AppData\Local\Temp\l3sy1453\Win11SDK_10.0.22621.00C0B50536C9040EC40F\Installers\Windows App Certification Kit x86 (OnecoreUAP)-x86_en-us.msi' succeeded. Hash: 455506811ECB09CA5DDF7DA5B422CF13A9F090CF073686657B6E0DFFA8DB58F0
[17e4:0019][2024-08-13T08:50:38] Checking SHA256 for path: C:\Users\Administrator\AppData\Local\Temp\l3sy1453\Win11SDK_10.0.22621.00C0B50536C9040EC40F\Installers\Windows App Certification Kit x86-x86_en-us.msi
[17e4:0019][2024-08-13T08:50:38] SHA256 verification for 'C:\Users\Administrator\AppData\Local\Temp\l3sy1453\Win11SDK_10.0.22621.00C0B50536C9040EC40F\Installers\Windows App Certification Kit x86-x86_en-us.msi' succeeded. Hash: 2E0D654E722CA2C43DC621D30D17FB41A99AE245EAAE1FF547C2931D94D0E9F7
[17e4:0019][2024-08-13T08:50:38] Checking SHA256 for path: C:\Users\Administrator\AppData\Local\Temp\l3sy1453\Win11SDK_10.0.22621.00C0B50536C9040EC40F\Installers\Windows Desktop Extension SDK Contracts-x86_en-us.msi
[17e4:0019][2024-08-13T08:50:38] SHA256 verification for 'C:\Users\Administrator\AppData\Local\Temp\l3sy1453\Win11SDK_10.0.22621.00C0B50536C9040EC40F\Installers\Windows Desktop Extension SDK Contracts-x86_en-us.msi' succeeded. Hash: A899AC101BF825C43FE3307D4E76FB502F0572644FA9FA91E93B2C3E24BF46EF
[17e4:0019][2024-08-13T08:50:38] Checking SHA256 for path: C:\Users\Administrator\AppData\Local\Temp\l3sy1453\Win11SDK_10.0.22621.00C0B50536C9040EC40F\Installers\Windows Desktop Extension SDK-x86_en-us.msi
[17e4:0019][2024-08-13T08:50:38] SHA256 verification for 'C:\Users\Administrator\AppData\Local\Temp\l3sy1453\Win11SDK_10.0.22621.00C0B50536C9040EC40F\Installers\Windows Desktop Extension SDK-x86_en-us.msi' succeeded. Hash: C80EB2F0D0A53EDE7FCD6E428793ABE939E8BC3CAA8EAF7DA95343D0934D39AF
[17e4:0019][2024-08-13T08:50:38] Checking SHA256 for path: C:\Users\Administrator\AppData\Local\Temp\l3sy1453\Win11SDK_10.0.22621.00C0B50536C9040EC40F\Installers\Windows IoT Extension SDK Contracts-x86_en-us.msi
[17e4:0019][2024-08-13T08:50:38] SHA256 verification for 'C:\Users\Administrator\AppData\Local\Temp\l3sy1453\Win11SDK_10.0.22621.00C0B50536C9040EC40F\Installers\Windows IoT Extension SDK Contracts-x86_en-us.msi' succeeded. Hash: 1C39908FA67AD8F8A89681C01243BA9AA74823600E5DA51A8FCEFC7BB84FC907
[17e4:0019][2024-08-13T08:50:38] Checking SHegory'    => false,
				'orderby'     => 'name',
				'limit'       => -1,
			)
		);
		$link_cats = get_terms( array( 'taxonomy' => 'link_category' ) );
		$limit     = (int) $instance['limit'];
		if ( ! $limit ) {
			$limit = -1;
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php _e( 'Select Link Category:' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>">
				<option value=""><?php _ex( 'All Links', 'links widget' ); ?></option>
				<?php foreach ( $link_cats as $link_cat ) : ?>
					<option value="<?php echo (int) $link_cat->term_id; ?>" <?php selected( $instance['category'], $link_cat->term_id ); ?>>
						<?php echo esc_html( $link_cat->name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<label for="<?php echo $this->get_field_id( 'orderby' ); ?>"><?php _e( 'Sort by:' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'orderby' ); ?>" id="<?php echo $this->get_field_id( 'orderby' ); ?>" class="widefat">
				<option value="name"<?php selected( $instance['orderby'], 'name' ); ?>><?php _e( 'Link title' ); ?></option>
				<option value="rating"<?php selected( $instance['orderby'], 'rating' ); ?>><?php _e( 'Link rating' ); ?></option>
				<option value="id"<?php selected( $instance['orderby'], 'id' ); ?>><?php _e( 'Link ID' ); ?></option>
				<option value="rand"<?php selected( $instance['orderby'], 'rand' ); ?>><?php _ex( 'Random', 'Links widget' ); ?></option>
			</select>
		</p>

		<p>
			<input class="checkbox" type="checkbox"<?php checked( $instance['images'], true ); ?> id="<?php echo $this->get_field_id( 'images' ); ?>" name="<?php echo $this->get_field_name( 'images' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'images' ); ?>"><?php _e( 'Show Link Image' ); ?></label>
			<br />

			<input class="checkbox" type="checkbox"<?php checked( $instance['name'], true ); ?> id="<?php echo $this->get_field_id( 'name' ); ?>" name="<?php echo $this->get_field_name( 'name' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'name' ); ?>"><?php _e( 'Show Link Name' ); ?></label>
			<br />

			<input class="checkbox" type="checkbox"<?php checked( $instance['description'], true ); ?> id="<?php echo $this->get_field_id( 'description' ); ?>" name="<?php echo $this->get_field_name( 'description' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'description' ); ?>"><?php _e( 'Show Link Description' ); ?></label>
			<br />

			<input class="checkbox" type="checkbox"<?php checked( $instance['rating'], true ); ?> id="<?php echo $this->get_field_id( 'rating' ); ?>" name="<?php echo $this->get_field_name( 'rating' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'rating' ); ?>"><?php _e( 'Show Link Rating' ); ?></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e( 'Number of links to show:' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php echo ( -1 !== $limit ) ? (int) $limit : ''; ?>" size="3" />
		</p>
		<?php
	}
}
