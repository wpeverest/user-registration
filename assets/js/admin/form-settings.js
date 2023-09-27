; ( function ( $ ) {

	var $this =  UR_Form_Settings = {
		init: function() {
			$( document ).ready( UR_Form_Settings.ready );
		},

		ready: function() {
			UR_Form_Settings.initSettings();
		},

		initSettings: function () {
			$this.initImageUploader();
		},

		initImageUploader: function () {
			$( '.form-row.ur-image-uploader' ).each(
				function ( _, row ) {

					$( row ).find( 'input' ).hide();
					var wrapper = $( row )
						.find( '.input-wrapper' )
						.append(
							'<div class="ur-image-uploader-wrapper">' +
							'<img src="#" style="display:none">' +
							'<div class="ur-image-uploader-btns-wrapper">' +
							'<button class="ur-file-upload-button button" style="display:none">Upload</button>' +
							'<button class="ur-file-remove-button button button-danger" style="display:none">Remove</button>' +
							'</div>' +
							'</div>'
					);

					var imageUrl = $( row ).find( 'input' ).val();

					if ( imageUrl.length ) {
						wrapper.find( 'img' ).attr( 'src', imageUrl ).show();
						wrapper.find( 'button.ur-file-remove-button' ).show();
					} else {
						wrapper.find( 'button.ur-file-upload-button' ).show();
					}

					wrapper.find( 'button.ur-file-upload-button' ).click( function ( e ) {
						e.preventDefault();
						var ur_uploader = $( this );

						var image = wp.media({
							library: {
								type: [ 'image' ]
							},
							title: ur_uploader.upload_file,
							multiple: false
						}).open()
							.on( 'select', function () {
							var uploaded_image = image.state().get('selection').first();
							var image_url = uploaded_image.toJSON().url;

							if ( image_url.length ) {
								ur_uploader.siblings( 'img' ).attr( 'src', image_url ).show();
								ur_uploader.closest( '.input-wrapper' ).find( 'input' ).attr( 'value', image_url );
								ur_uploader.siblings( 'button.ur-file-remove-button' ).show();
								ur_uploader.hide();
							}
						});
					} );

					wrapper.find( 'button.ur-file-remove-button' ).click(
						function ( e ) {
							e.preventDefault();

							$( this ).closest( '.input-wrapper' ).find( 'input' ).attr( 'value', '' );
							$( this ).siblings( 'img' ).hide();
							$( this ).hide();
							$( this ).siblings( 'button.ur-file-upload-button' ).show();
						}
					)
				}
			);
		}
	}

	UR_Form_Settings.init();
} )( jQuery );