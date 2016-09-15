<?php

final class SS_WC_MailChimp_Migration extends SS_WC_MailChimp_Migration_Base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * [up description]
	 * @return [type] [description]
	 */
	public function up() {

		$interest_groupings_key = 'interest_groupings';
		$groups_key = 'groups';

		try {

			if ( !array_key_exists( $interest_groupings_key, $this->settings ) || !array_key_exists( $groups_key, $this->settings ) ) return;

			$list = $this->settings['list'];
			$interest_groupings = $this->settings[$interest_groupings_key];
			$groups = $this->settings[$groups_key];

			if ( empty( $interest_groupings ) || empty( $groups ) || empty( $list ) ) return;

			$interest_categories = $this->api->get_interest_categories( $list );

			$selected_interest_category = array_filter( $interest_categories, function($v) use($interest_groupings) {
				return $v == $interest_groupings;
			} );

			$selected_interest_category_id = key( $selected_interest_category );

			$interest_category_interests = $this->api->get_interest_category_interests( $list, $selected_interest_category_id );

			$groups = explode( ',', $groups );

			$selected_interest_category_interests = array_intersect( $interest_category_interests, $groups );

			$interests = array_keys($selected_interest_category_interests);

			$this->settings['interests'] = $interests;

			unset($this->settings[$interest_groupings_key]);
			unset($this->settings[$groups_key]);

			$this->save_settings();
		} catch (Exception $e) {
			// If all else fails, finish the upgrade and the user will have to reselect the interest groups.
			unset($this->settings[$interest_groupings_key]);
			unset($this->settings[$groups_key]);

			$this->save_settings();
		}

		return true;

	}

	/**
	 * [down description]
	 * @return [type] [description]
	 */
	public function down() {

		throw new Exception('Cannot rollback to versions prior to 1.4.0');

	}

}