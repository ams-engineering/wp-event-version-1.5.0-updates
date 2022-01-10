/**
 * All of the code for your admin-facing JavaScript source
 * should reside in this file.
 */

jQuery(document).ready( function($) {
	
	const startDate = document.getElementById('wpevent-start-date');
	const endDate	= document.getElementById('wpevent-end-date');
	const phone		= document.getElementById('wpevent-phone');
	const seats		= document.getElementById('wpevent-seats');

	/**
	 * Ajax call for updating location data in metaboxes
	 * 
	 * @param {string} selectorID 
	 */
	function getLocationData( selectorID ) {
		var postID = $("#post_ID").val();
		$.ajax( {
			type: 'post',
			url:  wpe_ajaxobject.ajaxurl,
			dataType: 'json',
			data: { action: 'wpe_update_location',
					locationID: selectorID,
					eventID: postID,
				},
			success: function( response ) {
				var loc = JSON.parse( response );
				$( '#wpevent-venue' ).val( loc.venue );
				$( '#wpevent-address' ).val( loc.address );
				$( '#wpevent-country' ).val( loc.country );
				$( '#wpevent-city' ).val( loc.city );
				$( '#wpevent-state' ).val( loc.state );
				$( '#wpevent-zip' ).val( loc.zip );
				$( '#wpevent-map-url' ).val( loc.map_url );
			},
			error: function (error) {
				var imgURL = wpe_ajaxobject.pluginsUrl + '/wp-events/assets/img/wpe-error.png';
				wpe_popup('Location not found', imgURL );
			}
		} );
	}

	/**
	 * ajax call for creating new location
	 */
	function saveNewLocation() {
		var locObject 	  = new Object();
		locObject.venue   = $( '#wpevent-venue' ).val();
		locObject.address = $( '#wpevent-address' ).val();
		locObject.country = $( '#wpevent-country :selected' ).text();
		locObject.city    = $( '#wpevent-city' ).val();
		locObject.state   = $( '#wpevent-state' ).val();
		locObject.zip 	  = $( '#wpevent-zip' ).val();
		$.ajax( {
			type: 'post',
			url:  wpe_ajaxobject.ajaxurl,
			dataType: 'json',
			data: { action: 'wpe_create_location',
					location: locObject,
				},
			success: function( response ) {
				if ( response === 'Location Already Exists!' || response === 'Please fill all fields!' ) {
					var imgURL = wpe_ajaxobject.pluginsUrl + '/wp-events/assets/img/wpe-error.png';
					wpe_popup( response, imgURL );
				} else {
					var imgURL = wpe_ajaxobject.pluginsUrl + '/wp-events/assets/img/wpe-success.png';
					wpe_popup('New Location Added Successfully.', imgURL );
					$( '.wpe-location-fields' ).fadeOut();
					$( '#wpe-location-btn' ).fadeIn();
					$('#wpevent-location').append($('<option>', {
						value: response,
						text: $( '#wpevent-venue' ).val(),
					}));
					$("#wpevent-location").val( response ).change();
				}
			},
			error: function (error) {
				var imgURL = wpe_ajaxobject.pluginsUrl + '/wp-events/assets/img/wpe-error.png';
				wpe_popup('Location could not be added.', imgURL );
			}
		} );
	}

	//Check validity of metaboxes
	function metaboxValidation( inputArr, e ) {
		inputArr.forEach( function( input ){
			var title	 = '';
			var getTitle = input.getAttribute("title");
			if ( !input.checkValidity() ) {
				if ( input.validationMessage.includes("format") && getTitle != null ) {
					title = getTitle;
					showError( input, input.validationMessage + ' ' + title )
				} else if ( input.validationMessage.includes("fill out") ) {
					for( var i = 0; i < input.labels.length; i++ ) {
						showError( input, input.labels[i].textContent + ' cannot be empty.' )
					}
				} else {
					showError( input, input.validationMessage )
				}
				e.stopImmediatePropagation();             
			}else {
				showSucces( input );
			}
		});
	}

	//Show input error messages
	function showError( input, message ) {
		const eventControl 	   = input.parentElement;
		if ( eventControl.classList.contains( 'success' ) ) {
			eventControl.classList.remove( 'success' );
		}
		eventControl.className = eventControl.className + ' the-error';
		const small			   = eventControl.querySelector('small');
		small.innerText		   = message;
	}

	//show success colour
	function showSucces( input ) {
		const eventControl	   = input.parentElement;
		if ( eventControl.classList.contains( 'the-error' ) ) {
			eventControl.classList.remove( 'the-error' );
		}
		eventControl.className = eventControl.className + ' success';
	}

	function locationChange() {
		var src 	 = $('#wpe-map-frame').attr("src");
		var url 	 = src.split( "q=" );
		var baseURL  = url[0];
		var venue 	 = $('#wpevent-loc-venue').val();
		venue 		 = venue.replace(/ /g, "+");
		var address  = $('#wpevent-loc-address').val();
		address 	 = address.replace(/ /g, "+");
		var city 	 = $('#wpevent-loc-city').val();
		city 		 = city.replace(/ /g, "+");
		var state 	 = $('#wpevent-loc-state').val();
		state 		 = state.replace(/ /g, "+");
		var location = venue + '+' + address + ',' + city + '+' + state;
		var newSrc   = baseURL.concat( 'q=', location );
		$('#wpe-map-frame').attr( "src", newSrc );
	}

	function wpeGetCood( location ) {
		var geo = new google.maps.Geocoder();
		geo.geocode(
			{ 'address': location }, 
			function( results, status ) {
				if ( status == google.maps.GeocoderStatus.OK ) {              
					var wpeLat = results[0].geometry.location.lat();
					var wpeLng = results[0].geometry.location.lng();
					$('#wpevent-logitude').val( wpeLng );
					$('#wpevent-latitude').val( wpeLat );
				}		
		});
	}
	  
	function geocodeLatLng() {
		const geocoder = new google.maps.Geocoder();
		const latlng   = {
		  lat: parseFloat( $('#wpevent-latitude').val() ),
		  lng: parseFloat( $('#wpevent-logitude').val() ),
		};
		geocoder
		.geocode({ location: latlng })
		.then((response) => {
			if (response.results[0]) {
				var newAddress = response.results[0].formatted_address;
				var addArray   = newAddress.split( ',' );
				$('#wpevent-loc-venue').val( '' );
				$('#wpevent-loc-address').val( addArray[0] );
				$('#wpevent-loc-city').val( addArray[1] );
				var zipState = addArray[2].split( ' ' );
				$('#wpevent-loc-state').val( zipState[1] );
				$('#wpevent-loc-zip').val( zipState[2] );
			} else {
			  	window.alert("No results found");
			}
		})
		.catch((e) => window.alert("Geocoder failed due to: " + e));
	}

	function updateLngLat() {
		var venue 	 = $('#wpevent-loc-venue').val();
		venue 		 = venue.replace(/ /g, "+");
		var address  = $('#wpevent-loc-address').val();
		address 	 = address.replace(/ /g, "+");
		var city 	 = $('#wpevent-loc-city').val();
		city 		 = city.replace(/ /g, "+");
		var state 	 = $('#wpevent-loc-state').val();
		state 		 = state.replace(/ /g, "+");
		var location = venue + '+' + address + ',' + city + '+' + state;
		wpeGetCood( location );
	}

	$('.wpevent-location').keyup( function( e ) {
		e.preventDefault();
		$('#wpe-no-map').addClass('wpe-hidden');
		$('#wpe-latlng-map').addClass('wpe-hidden');
		$('#wpe-address-map').removeClass('wpe-hidden');
		setTimeout( locationChange, 1000 );
	});

	$('.wpevent-location').change( function( e ) {
		e.preventDefault();
		updateLngLat();
	});

	$('.wpevent-latlng').change( function( e ) {
		e.preventDefault();
		$('#wpe-no-map').addClass('wpe-hidden');
		$('#wpe-address-map').addClass('wpe-hidden');
		$('#wpe-latlng-map').removeClass('wpe-hidden');
		var src 	= $('#wpevent-map-frame').attr("src");
		var url 	= src.split( "q=" );
		var baseURL = url[0];
		var lat 	= $('#wpevent-latitude').val();
		var lng 	= $('#wpevent-logitude').val();
		var latlng  = lat + ',' + lng;
		var newSrc  = baseURL.concat( 'q=', latlng );
		$('#wpevent-map-frame').attr( "src", newSrc );
		geocodeLatLng();
	});

	$('#wpe-location-btn').click( function( e ) {
		e.preventDefault();
		$('.wpe-location-field').val('');
		$("#wpevent-location").val("");
		$( '.wpe-location-fields' ).fadeIn();
		$( this ).fadeOut();
	});

	$('#wpe-save-location').click( function( e ) {
		e.preventDefault();
		saveNewLocation();
	});

	$('#wpevent-location').change( function( e ) {
		e.preventDefault();
		getLocationData( $(this).val() );
	});

	if ( $('body').hasClass('post-type-locations') ) {
		$(window).load(function() {
			updateLngLat();
			var venue 	= $('#wpevent-loc-venue').val();
			var address = $('#wpevent-loc-address').val();
			var city 	= $('#wpevent-loc-city').val();
			var state 	= $('#wpevent-loc-state').val();
			var lat 	= $('#wpevent-latitude').val();
			var lng 	= $('#wpevent-logitude').val();
			if( ( venue === '' && address === '' && city === '' && state === '' ) && ( lat === '' && lng === '' ) ) {
				$('#wpe-address-map').addClass('wpe-hidden');
				$('#wpe-latlng-map').addClass('wpe-hidden');
				$('#wpe-no-map').removeClass('wpe-hidden');
			} else {
				$('#wpe-no-map').addClass('wpe-hidden');
			}
		});

		//Require post title when adding/editing Project Summaries
		$( 'body' ).on( 'submit.edit-post', '#post', function () {

			// If the title isn't set
			if ( $( "#title" ).val().replace( / /g, '' ).length === 0 ) {

				// Show the alert
				wpe_popup( 'A title is required.' );

				// Hide the spinner
				$( '#major-publishing-actions .spinner' ).hide();

				// The buttons get "disabled" added to them on submit. Remove that class.
				$( '#major-publishing-actions' ).find( ':button, :submit, a.submitdelete, #post-preview' ).removeClass( 'disabled' );

				// Focus on the title field.
				$( "#title" ).focus();

				return false;
			}
		});
	}

	//on event single page
	if ( $('body').hasClass('post-type-wp_events') ) {
		// on publish button click
		$(document).on( 'click', '.editor-post-publish-button', function(e){

			metaboxValidation( [startDate, endDate, seats, phone], e );

		});

		//disable next/prev controls if corresponding entries don't exist
		$(window).load(function() {
			var linkPrevious = $('#wpe-entry-previous').attr('href');
			var linkNext	 = $('#wpe-entry-next').attr('href');
			if( linkPrevious == '#' ) {
				$('#wpe-entry-previous').addClass('isDisabled');
			}
			if( linkNext == '#' ) {
				$('#wpe-entry-next').addClass('isDisabled');
			}
		});

		/*filter events by type when event status is future, past
		 *or ongoing.
		 */
		$('#post-query-submit').click( function( e ) {
			var param = "event_status=";
			var url   = window.location.href;
			var type  = $('#wp_events_type').val();
			var date  = $('#filter-by-date').val();
			if ( url.indexOf( param ) !== -1 ) {
				e.preventDefault();
				window.location.href = url + '&wp_events_type=' + type + '&m=' + date;
			}
		});
	}

	function wpe_datepicker() {
		$( "#wpevent-start-date" ).datepicker(
			{ dateFormat : 'yy-mm-dd' }
		);

		$( "#wpevent-end-date" ).datepicker(
			{ dateFormat : 'yy-mm-dd' }
		);

		$( "#wpe-filter-start-date" ).datepicker(
			{ dateFormat : 'yy-mm-dd' }
		);

		$( "#wpe-filter-end-date" ).datepicker(
			{ dateFormat : 'yy-mm-dd' }
		);

	}
	 
	// Initialize select2
	$("#wpe_titles").select2();
	$("#wpe_categories").select2();

	/**
	 * Contains all the functions executed on change events of start date,
	 * start time, end date and end time
	 */
	function date_validation() {

	var startDateSelector = '.wpevent-start-date';	
	var endDateSelector	  = '.wpevent-end-date';	
	var startTimeSelector = '.wpevent-start-time';	
	var endTimeSelector	  = '.wpevent-end-time';

	var verifyDateTime = new dateValidation( startDateSelector, endDateSelector, startTimeSelector, endTimeSelector );

	verifyDateTime.changeEndDate();
	verifyDateTime.changeStartDate();
	verifyDateTime.changeEndTime();
	verifyDateTime.changeStartTime();
	
	}

	// on upload button click
	$(document).on( 'click', '.wpe-upl', function(e){

		e.preventDefault();

		var button = $(this),
			custom_uploader = wp.media({
				title: 'Insert image',
				library : {
					// uploadedTo : wp.media.view.settings.post.id, // attach to the current post?
					type : 'image'
				},
				button: {
					text: 'Use this image' // button label text
				},
				multiple: false
			}).on('select', function() { // it also has "open" and "close" events
				var attachment = custom_uploader.state().get('selection').first().toJSON();
				$(".wpe-image-val").val( attachment.id );
				button.html('<img height="150" width="150" name="wpe_display_settings[image_id]" src="' + attachment.url + '">').next().val(attachment.id).next().show();
			}).open();
			$('.wpe-rmv').css('display','block');

	});

	// on remove button click
	$(document).on('click', '.wpe-rmv', function(e){

		e.preventDefault();

		var button = $(this);
		button.next().val(''); // emptying the hidden field
		button.hide().prev().html('Upload image');
	});

	// on export button click
	$("#wpe_export_events").click( function() {
		$.ajax( {
			type: 'POST',
		    url: wpe_ajaxobject.ajaxurl,
		    dataType: "json", // add data type
		    cache: false,
		    data: { action : 'wp_get_ajax_events',
		    postStatus: $( "#post_status" ).val() },
		    beforeSend: function() {
		       $( "#wpe_export_events" ).attr( "disabled", true );
		    },
		    success: function( response ) {
				window.open( response );
				deleteFile( response );
		    },
		    complete: function() {
		      $( "#wpe_export_events" ).removeAttr( "disabled" );
		    }   
		});

	});

	/**
	 * Event type select change
	 *
	 * Hides location info on the select of webinar
	 * */
	$('#event-type').on('change', function (e) {
		let valueSelected = this.value;
		if( valueSelected === 'webinar' ) {
			$( '.wp-events-location' ).fadeOut();
			$( '.wpe-map-div' ).fadeOut();
			var message = wpe_ajaxobject.webinarMessage;
			$( '#wpevent-confirmation-message' ).text(message);
		} else {
			$( '.wp-events-location' ).fadeIn();
			$( '.wpe-map-div' ).fadeIn();
			var message = wpe_ajaxobject.seminarMessage;
			$( '#wpevent-confirmation-message' ).text(message);
		}
	});

	/**
	 * Mail accordion
	 * */
	jQuery( document ).ready( function () {
		jQuery( ".other-hold" ).click( function () {
			if (jQuery( this ).hasClass( "active" ) ) {
				jQuery( this ).removeClass( "active" );
				jQuery( this ).next().slideUp( 700 );
			} else {
				jQuery( this ).addClass( "active" );
				jQuery( this ).next().slideDown( 700 );
			}
		});


	});

	// on Edit registration button click
	$(document).on('click', '.wpe-edit-registration', function(e){

		e.preventDefault();
		$('.wpe-edit-entry-form').removeClass('disabledform');
		$('#wpe_texting-id').removeAttr('disabled');
		$('#first_name-id').focus();
		$('.wpe-edit-registration').text('Save');
		$('.wpe-edit-registration').addClass('wpe-save-registration');
		$('.wpe-edit-form-button').removeClass('wpe-hidden');
		$('.wpe-edit-registration').removeClass('wpe-edit-registration');
	});

	$('#wpe-edit-entry-form').submit(function (e) {
		e.preventDefault();
	  });

	// on Save registration button click
	$( document ).on('click', '.wpe-save-registration', function(e) {
		e.preventDefault();
		let searchParams  = new URLSearchParams( window.location.search );
		let tab			  = searchParams.get('tab');
		if ( tab === 'registrations' ) {
			var seats  = $('#wpe_seats-id').val();
			var guests = $('#guests-id').val();
			var count  = guests.split(',').filter( (i) => i.length ).length;
			if( seats > 1 ) {
				if( count > ( seats-1 ) ) {
					wpe_popup('Only ' + ( seats-1 ) + ' guest(s) are allowed.' );
				} else if( count < ( seats-1 ) ) {
					wpe_popup('Please enter complete guest information.');
				} else {
					saveEntry();
				}
			} else {
				$('#guests-id').val('');
				saveEntry();
			}
		} else {
			var textingPerm = $('#wpe_texting-id').prop('checked');
			saveEntry( textingPerm );
		}
	});

	$( document ).on('click', '.wpe-action-icon', function(){
		$(this).parent().parent().next().slideUp( 300 );
		$(this).removeClass('dashicons-arrow-up');
		$(this).addClass('dashicons-arrow-down');
	});

	$( document ).on('click', '.dashicons-arrow-down', function(){
		$(this).parent().parent().next().slideDown( 300 );
		$(this).addClass('dashicons-arrow-up');
		$(this).removeClass('dashicons-arrow-down');
	});

	//ajax call to update form data after entry is edited
	function updateEntry( dataString, textingPerm ){
		$.ajax( {
		  	type: 'post',
			url:  wpe_ajaxobject.ajaxurl,
			data: { action: 'wpe_update_entry',
					formData: dataString,
					permissions: textingPerm
				},
			success: function( response ) {
				if ( response != '0000' ) {
					var imgURL = wpe_ajaxobject.pluginsUrl + '/wp-events/assets/img/wpe-success.png';
					wpe_popup( 'Record Updated Successfully.', imgURL );
				} else {
					var imgURL = wpe_ajaxobject.pluginsUrl + '/wp-events/assets/img/wpe-error.png';
					wpe_popup('Could not update data!', imgURL );
				}
				$('.wpe-edit-entry-form').addClass('disabledform');
				$('.wpe-save-registration').text('Edit');
				$('.wpe-save-registration').addClass('wpe-edit-registration');
				$('.wpe-save-registration').removeClass('wpe-save-registration');
			},
			error: function (error) {
				var imgURL = wpe_ajaxobject.pluginsUrl + '/wp-events/assets/img/wpe-error.png';
				wpe_popup('Could not update data!', imgURL );
			}
		} );
	}

	// on Resend notification button click
	$( document ).on('click', '#resend-btn', function(e) {
		e.preventDefault();
		var form		  = document.getElementById( 'wpe-edit-entry-form' );
		var dataString	  = $( form ).serializeJSON();
		adminNotification = $('#wpe-entry-notification').prop("checked");
		let searchParams  = new URLSearchParams( window.location.search );
		let tab			  = searchParams.get('tab');
		resendNotification( dataString, adminNotification, tab );
	});

	//ajax call to update form data after entry is edited
	function resendNotification( dataString, adminNotification, tab ){
		$.ajax( {
		  	type: 'post',
			url:  wpe_ajaxobject.ajaxurl,
			data: { action: 'wpe_resend_notification',
					formData: dataString,
					adminNoti: adminNotification,
					displayTab: tab
				},
			success: function( response ) {
				if( response == 1 ) {
					wpe_popup('Notification Resent.');
				} else {
					wpe_popup('Could Not Process Request.');
				}
			},
			error: function ( error ) {
				wpe_popup('Could Not Process Request');
			}
		} );
	}

	// on Resend notification button click
	$( document ).on('click', '.wpe-to-trash', function(e) {
		e.preventDefault();
		buttonText		 = $(this).text();
		let searchParams = new URLSearchParams( window.location.search );
		let tab			 = searchParams.get('tab');
		let entry		 = searchParams.get('entry');
		trashRestoreButton( buttonText, tab, entry );
	});

	$('#wpe_approve_registrations').change( function() {
		$checkbox = $(this).prop("checked");
		$.ajax( {
			type: 'post',
			url:  wpe_ajaxobject.ajaxurl,
			data: { action: 'wpe_update_entry_status',
					checkbox: $checkbox },
			success: function( response ) {
				// alert(response);
				if( response != 1 ) {
					wpe_popup('Could Not Process Request');
				}
			},
			error: function ( error ) {
				wpe_popup('Could Not Process Request');
			}
		} );
	});

	//ajax call to update form data after entry is edited
	function trashRestoreButton( buttonText, tab, entry ){
		$.ajax( {
		  	type: 'post',
			url:  wpe_ajaxobject.ajaxurl,
			data: { text: buttonText,
					entryID: entry,
					displayTab: tab,
					action: 'wpe_trash_restore' },
			success: function( response ) {
				if( response == 1 ) {
					if( buttonText === 'Move To Trash' ) {
						wpe_popup('Entry moved to Trash.');
						$('.wpe-to-trash').text('Restore');
						$('.wpe-entry-status').text('Entry Status: Trash');
					} else {
						wpe_popup('Entry restored.');
						$('.wpe-to-trash').text('Move To Trash');
						if( tab === 'registrations' ) {
							$('.wpe-entry-status').text('Entry Status: Pending Approval');
						} else {
							$('.wpe-entry-status').text('Entry Status: Active');
						}
					}
				}
			},
			error: function ( error ) {
				wpe_popup('Could Not Process Request');
			}
		} );
	}

	function saveEntry( textingPerm = false ) {
		$( "#wpe-edit-entry-form" ).submit();
		$('#wpe_texting-id').attr('disabled', true);
		var form = document.getElementById( 'wpe-edit-entry-form' );
		var dataString = $( form ).serializeJSON();
		updateEntry( dataString, textingPerm );
	}

	//display popup on current page
	function wpe_popup( message, image = 0 ) {
		$('body').prepend('<div class="wpe-popup"><div class="popup-inner"><span class="close-btn"></span><p>' + message + '</p></div></div>');
		if ( image != 0 ) {
			$('.popup-inner').prepend( '<img src="' + image + '">' );
		}
		setTimeout(function () {
			$('.wpe-popup').fadeOut();
		}, 3000);
	}

	/**
	 * Additional Guests info
	 * */
	$('#wpe_seats-id').on('change', function () {
		var seats = $(this).val();
		if( seats == 1 ) {
			$('.guest-div').removeClass('wpe-show');
			$('.guest-div').addClass('wpe-hidden');
		} else {
			$('.guest-div').addClass('wpe-show');
			$('.guest-div').removeClass('wpe-hidden');
			$('.guest-div small').removeClass('wpe-hidden');
			$('.guest-div small').css('visibility', 'visible');
			$('.guest-div small').css('color', 'black');
			$('.guest-div small').text('Enter ' + ( seats-1 ) + ' comma separated name(s) for guest(s).');
		}
	});

	$('#wpe_subscriber_enable_phone_number').change( function() {
		if ( $( this ).prop("checked") ) {
			$( '#wpe_subscriber_enable_texting_permission' ).removeAttr('disabled');
		} else {
			$( '#wpe_subscriber_enable_texting_permission' ).removeAttr('checked');
			$( '#wpe_subscriber_enable_texting_permission' ).attr("disabled", true);
		}
	});


	/**
	*  Export entries
	*/
	$("#export-event-entries").on( "click", function(e) {
		e.preventDefault();
		var WpeStartDate = $("#wpe-filter-start-date").val();
		var WpeEndDate 	 = $("#wpe-filter-end-date").val();
		var WpeEvent 	 = $("#wpe_titles").val();

		$.ajax( {
			type: 'POST',
		    url: wpe_ajaxobject.ajaxurl,
		    dataType: "json", // add data type
		    cache: false,
		    data: { action : 'wpe_event_entries',
		    Startdate: WpeStartDate,
			Enddate: WpeEndDate,
			wpeeventid: WpeEvent },
		    beforeSend: function() {
		       $( "#export-event-entries" ).attr( "disabled", true );
		    },
		    success: function( response ) {
				window.open( response );
				deleteFile( response );
		    },
		    complete: function() {
		      $( "#export-event-entries" ).removeAttr( "disabled" );
		    }   
		});
		
	});

	/**
	*  Export entries for Subscriptions
	*/
	$("#export-subscription").on("click", function(e) {
		e.preventDefault();

		$.ajax( {
			type: 'POST',
		    url: wpe_ajaxobject.ajaxurl,
		    dataType: "json", // add data type
		    cache: false,
		    data: { action : 'wpe_export_subscription'
		},
		    beforeSend: function() {
		       $( "#export-subscription" ).attr( "disabled", true );
		    },
		    success: function( response ) {
				window.open( response );
				deleteFile( response );
		    },
		    complete: function() {
		      $( "#export-subscription" ).removeAttr( "disabled" );
		    }   
		});
		
	});

	function deleteFile( fileURL ) {
		$.ajax( {
			type: 'POST',
		    url: wpe_ajaxobject.ajaxurl,
		    dataType: "text", // add data type
		    cache: false,
		    data: { action : 'wpe_delete_file',
					url : fileURL },
		    success: function( response ) {
		    },  
		});
	}

	$("#wpe_import_events").click( function() {
		var fd    = new FormData();
		var files = $('#wpe_file_upload')[0].files;
		
		// Check file selected or not
		if( files.length > 0 ) {
			fd.append( 'fileUpload', files[0] );
		    fd.append( "action" , 'wpe_upload_file'); 
		    fd.append( "nonce" , wpe_ajaxobject.wpeAjaxNonce); 
		    $.ajax({
				url: wpe_ajaxobject.ajaxurl,
				type: 'post',
				data: fd,
				contentType : false,
				processData : false,
				enctype: 'multipart/form-data',
				success: function( response ) {
					switch( response ) {
						case 2:
							alert('Sorry, your file is too large.');
							break;
						case 3:
							alert('Sorry, only CSV files are allowed.');
							break;
						case 4:
							alert('Sorry, there was an error uploading your file.');
							break;
						case 5:
							alert('Something went wrong, try again.');
							break;
						case 6:
							alert('File format is not correct');
							break;
						default:
							alert('File imported successfully!!');
							deleteFile( response );
							$("#wpe_file_upload").val(null);
					}
				},
			});
		} else {
			alert("Please select a file.");
		}
	});

	if( window.location.href.includes('tab=export') || window.location.href.includes('tab=import') ) {
		$('#submit').css( 'display', 'none' );
	}

	//function calls
	wpe_datepicker();
	date_validation();
} );