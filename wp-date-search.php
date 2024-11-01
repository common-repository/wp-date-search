<?php

/*
Plugin Name: WP Date Search
Description: Adds in date searching to the standard WordPress search.
Version: 1.0.0
Author: Paul Johnson
Author URI: http://www.paulhowardjohnson.com
Text Domain: wp-date-search
License: GPLv2 or later
*/

/**
 * Does the magic
 */
function wp_date_search_add_date_search(&$query) {
	if (!is_admin() && $query->is_main_query()) {
	    if ($query->is_search) {
	    	if (!empty($query->query_vars['s'])) {

	    		// First, check for just a month name.
	    		$possible_month = strtolower($query->query_vars['s']);
	    		$months = array(
	    			'january',
	    			'february',
	    			'march',
	    			'april',
	    			'may',
	    			'june',
	    			'july',
	    			'august',
	    			'september',
	    			'october',
	    			'november',
	    			'december'
	    		);
	    		$year_regex = '/^(19|20)\d{2}$/';

	    		if (in_array($possible_month, $months)) {

	    			// We have found a month query.  If the current month is less than
	    			// the month, look for this year.  Otherwise, look for last year.
	    			$month_to_search = array_search($possible_month, $months) + 1;
	    			$current_month = (int)date('n');
	    			$year_to_search = (int)date('Y');

	    			if ($current_month < $month_to_search) {
	    				$year_to_search--;
	    			}	

		    		// Now set up the query args.
		    		$query->query_vars['date_query'] = array(
		      			array(
		      				'month' => $month_to_search,
		      				'year' => $year_to_search
		      			)   
		      		);   

					$query->query_vars['s'] = '';
				} else {

					// Check for a month and year.
					list($possible_month, $possible_year) = explode(' ', $query->query_vars['s']);
					$possible_check_month_i = array_search(strtolower($possible_month), $months);

					if ($possible_check_month_i !== false && preg_match($year_regex, $possible_year)) {

						// We found a month and year combination.  Set up the search variables.
						$query->query_vars['date_query'] = array(
		      				array(
		      					'month' => $possible_check_month_i + 1,
		      					'year' => (int)$possible_year
		      				)
		      			);	     	
			      		
						$query->query_vars['s'] = '';
					} else {

						// Look for just a year.
						if (preg_match($year_regex, $query->query_vars['s'])) {

							// Set up the year query vars.
							$query->query_vars['date_query'] = array(
			      				array(
			      					'year' => (int)$query->query_vars['s']
			      				)
			      			);	     	
			      		
							$query->query_vars['s'] = '';
						} else {

							// Finally, look for an exact date.
						    $possible_time = strtotime($query->query_vars['s']);

					      	if ($possible_time !== false) {
								$query->query_vars['date_query'] = array(
					      			array(
					      				'month' => (int)date('n', $possible_time),
					      				'day' => (int)date('j', $possible_time),
					      				'year' => (int)date('Y', $possible_time)
					      			)   
					      		);   	
					      		
								$query->query_vars['s'] = '';
					      	}
					    }
					}
			    }
		    }
		}
	}
}

add_action('pre_get_posts', 'wp_date_search_add_date_search');