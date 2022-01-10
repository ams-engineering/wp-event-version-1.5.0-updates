class dateValidation {

	/**
	 * Class constructor
	 * 
	 * @param sds start date selector
	 * @param eds end date selector
	 * @param sts start time selector
	 * @param ets end time selector 
	 * 
	 * @since 1.2.0
	 */
	constructor( sds, eds, sts, ets ) {
		this.sds = sds;
		this.eds = eds;
		this.sts = sts;
		this.ets = ets;
	}
	/**
	 * gets date from date object and return in form of formatted string
	 * 
	 * @since 1.2.0
	 * 
	 * @param {object} date 
	 * @returns {string}
	 */
	 createDateString( date ) {
		var dd 	   = String( date. getDate() ). padStart( 2, '0' );
		var mm 	   = String( date. getMonth() + 1 ). padStart( 2, '0' ); //January is 0!
		var yyyy   = date. getFullYear();
		var dateString = yyyy + '-' + mm + '-' + dd;
		return dateString;
	}

	/**
	 * gets time from date object and return in form of formatted string
	 * 
	 * @since 1.2.0
	 * 
	 * @param {object} time 
	 * @returns {string}
	 */
	createTimeString( time ) {
		var hours   = time.getHours();
		var minutes = time.getMinutes();
		if ( minutes < 10 ) {
			minutes = '0' + minutes;
		}
		if ( hours < 10 ) {
			hours   = '0' + hours;
		}
		var timeString = hours + ':' + minutes;
		return timeString;
	}

	//Adds days to given date object
	addDays( date, days ) {
		const copy = new Date( Number( date ) )
		copy.setDate( date.getDate() + days )
		return copy
	}

	//Adds number of minutes passed to the date object
	add_minutes( dt, minutes ) {
		return new Date( dt.getTime() + minutes*60000 );
	}

	//Checks if start date and end date are empty
	emptyDates ( startDate, endDate ) {
		if( startDate === '' && endDate === '' ) {
			return true;
		}
		return false;
	}

	//Checks if start date and end date are equal (same day event)
	equalDates( startDate, endDate ) {
		if( startDate === endDate ) {
			return true;
		}
		return false;
	}

	/**
	 * Handles change event of end date
	 */
	changeEndDate() {
		const $this = this;
		/**
		 * Compare Event Dates 
		 * 
		 * Check if end date is larger than start date
		 */
		jQuery( $this.eds ).change( function() {
			var startDate 	  = jQuery( $this.sds ).val();
			var endDate 	  = jQuery( $this.eds ).val();
			var startTime 	  = jQuery( $this.sts ).val();
			var endTime		  = jQuery( $this.ets ).val();
			var startDateTime = new Date( startDate + ' ' + startTime );
			var today 		  = new Date();
			var currentDate   = $this.createDateString( today );
			var todayMs 	  = Date.parse( today );
			var endDateTime   = new Date( endDate + ' ' + endTime );
			var endMs 		  = Date.parse( endDateTime );
			
			jQuery( $this.eds ).attr({
				"min" : startDate
			 });

			//if start date is not empty
			if ( startDate != '' ) {
				//If start and end dates are equal and start time is greater than end time
				if ( $this.equalDates( startDate, endDate ) && startTime > endTime ) {
					var newTime = $this.createTimeString( $this.add_minutes( startDateTime, 60 ) );
					jQuery( $this.ets ).val( newTime );
				}
				if ( startDate > endDate ) {
					jQuery( $this.sds ).val( endDate );
				}
				//If end date is equal to current date and end time is less than current time
				if ( endDate === currentDate && endMs < todayMs ) {
					var newEndTime = $this.createTimeString( $this.add_minutes( today, 60 ) );
					jQuery( $this.ets ).val( newEndTime );
				}
			} else {
				jQuery( $this.sds ).val( endDate );
			}
		});
	}

	/**
	 * Handles change event of start date
	 */
	changeStartDate() {	
		const $this = this;
		/**
		 * Stores value of start date before change
		 */
		jQuery( $this.sds ).on( 'focusin', function() {
			jQuery(this).data( 'val', jQuery(this).val() );
		});
		/**
		 * Compare Event Dates 
		 * 
		 * Check if start date is smaller than end date
		 */
		 jQuery( $this.sds ).change(function(){
			var startDate 			= jQuery( $this.sds ).val();
			var endDate 			= jQuery( $this.eds ).val();
			var startTime 			= jQuery( $this.sts ).val();
			var endTime 			= jQuery( $this.ets ).val();
			var oldStartDate 		= jQuery(this).data('val');
			if( oldStartDate === '' ) {
				oldStartDate = startDate;
			}
			if( startDate === '' ) {
				startDate = oldStartDate;
			}
			var date1 				= new Date( startDate );
			var date2 				= new Date( oldStartDate );
			var Difference_In_Time 	= date1.getTime() - date2.getTime();
			var Difference_In_Days 	= Difference_In_Time / (1000 * 3600 * 24);
			const date 				= new Date( endDate );
			const newEndDate 		= $this.addDays( date, Difference_In_Days );
			var startDateTime 		= new Date( startDate + ' ' + startTime );
			var today 		  		= new Date();
			var currentDate 	  	= $this.createDateString( today );
			var todayMs 	  		= Date.parse( today );
			var endDateTime   		= new Date( endDate + ' ' + endTime );
			var endMs 		  		= Date.parse( endDateTime );

			jQuery( $this.eds ).datepicker( "option", "minDate", startDate );
			jQuery( $this.eds ).attr({
				"min" : startDate
			 });
			//If end date is equal to current date and end time is less than current time
			if ( endDate === currentDate && endMs < todayMs ) {
				var newEndTime = $this.createTimeString( $this.add_minutes( today, 60 ) );
				jQuery( $this.ets ).val( newEndTime );
			}
			//if end date is not empty
			if ( endDate !== '' ) {
				//if start date is greater than end date
				if ( startDate > endDate ) {
					var endDateNew = $this.createDateString( newEndDate );
					if( Difference_In_Days == 0 ) {
						jQuery( $this.eds ).val( startDate );
					}else {
						jQuery( $this.eds ).val( endDateNew );
					}
				}
				//if both dates are equal (one day event) and start time is greater than end time
				if ( $this.equalDates( startDate, endDate ) && startTime > endTime ) {
					var newTime = $this.createTimeString( $this.add_minutes( startDateTime, 60 ) );
					jQuery( $this.ets ).val( newTime );
				}
			}else {
				jQuery( $this.eds ).val( startDate );
				jQuery( $this.sds ).trigger( "change" );
			}
		});
	}

	/**
	 * Handles change event of end time
	 */
	changeEndTime() {
		const $this = this;
		/**
		 * Compare Event Times 
		 * 
		 * Check if end time is larger than start time for same day events
		 * 
		 */
		 jQuery( $this.ets ).change( function() {
			var startDate 	  = jQuery( $this.sds ).val();
			var endDate 	  = jQuery( $this.eds ).val();
			var startTime 	  = jQuery( $this.sts ).val();
			var endTime 	  = jQuery( $this.ets ).val();
			var today 		  = new Date();
			var currentDate   = $this.createDateString( today );
			var todayMs 	  = Date.parse( today );
			var endDateTime   = new Date( endDate + ' ' + endTime );
			var endMs 		  = Date.parse( endDateTime );
			var startDateTime = new Date( startDate + ' ' + startTime );

			//if both dates and start time is set and both dates are equal(one day event)
			if ( ! $this.emptyDates( startDate, endDate ) && $this.equalDates( startDate, endDate ) && startTime !== '' ) {
				//if start time is greater than or equal to end time
				if ( startTime >= endTime ) {
					var neweTime = $this.createTimeString( $this.add_minutes( startDateTime, 60 ) );
					alert('End Time should be greater than Start time for same day events.');
					jQuery( $this.ets ).val( neweTime );
				}//if end date is equal to current date and end time is less than or equal to current time
				else if ( endDate === currentDate && endMs <= todayMs ) {
					alert('End Time should be greater than Current Time if event is today.');
					var newEndTime = $this.createTimeString( $this.add_minutes( today, 60 ) );
					jQuery( $this.ets ).val( newEndTime );
				}
			} //if both dates and start time is set and end date is equal to current date
			else if ( ! $this.emptyDates( startDate, endDate ) && endDate === currentDate && startTime !== '' ) {
				//if end time is less than or equal to current time
				if ( endMs <= todayMs ) {
					alert('End Time should be greater than Current Time if event is today.');
					var newEndTime = $this.createTimeString( $this.add_minutes( today, 60 ) );
					jQuery( $this.ets ).val( newEndTime );
				}
			} //if both dates are empty and end time is less than start time
			else if ( $this.emptyDates( startDate, endDate ) && endTime < startTime ) {
				jQuery( $this.ets ).val( startTime );
			}
		});
	}

	/**
	 * Handles change event of start time
	 */
	changeStartTime() {
		const $this = this;
		/**
		 * Compare Event Times 
		 * 
		 * Check if start time is smaller than end time for same day events
		 */
		 jQuery( $this.sts ).change(function(){
			var startDate 	= jQuery( $this.sds ).val();
			var endDate 	= jQuery( $this.eds ).val();
			var startTime   = jQuery( $this.sts ).val();
			var endTime 	= jQuery( $this.ets ).val();
			var today 		= new Date();
			var currentDate = $this.createDateString( today );
			var todayMs 	= Date.parse( today );
			var endDateTime = new Date( endDate + ' ' + endTime );
			var endMs 		= Date.parse( endDateTime );
			jQuery( $this.ets ).attr( 'min', startTime );

			//if both dates and end time is set and dates are equal (one daye event)
			if ( ! $this.emptyDates( startDate, endDate ) && $this.equalDates( startDate, endDate ) && endTime !== '' ) {
				//if end date is equal to current date and end time is less than current time
				if ( endDate === currentDate && endMs < todayMs ) {
					var newEndTime = $this.createTimeString( $this.add_minutes( today, 60 ) );
					jQuery( $this.ets ).val( newEndTime );
				} //if start time is greater than or equal to end time
				else if ( startTime >= endTime ) {
					alert('Start Time should be smaller than End Time for same day events.');
					jQuery( $this.ets ).val( startTime );
				} 
			}//if both dates are empty and end time is less than start time 
			else if ( $this.emptyDates( startDate, endDate ) && endTime < startTime ) {
				jQuery( $this.ets ).val( startTime );
			}
		});
	}
}