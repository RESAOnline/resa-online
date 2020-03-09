<h1><?php echo get_admin_page_title() ?></h1>

<div class="container" id="resa_form" ng-app="resa_app" ng-controller="ReductionsController as reductionsCtrl"
	ng-init='reductionsCtrl.initialize(<?php echo $variables['days']; ?>,
										<?php echo $variables['languages']; ?>,
										"<?php echo get_locale(); ?>",
										"<?php echo admin_url('admin-ajax.php'); ?>")'  ng-cloak>
	<div ng-show="reductionsCtrl.loadingData" class="ro-loader-back">
		<span class="ro-span"> </span>
		<div class='ro-loader-img' style='transform:scale(0.35);'><div></div><div></div></div>
	</div>
   <div class="row">
		<div uib-alert ng-if="reductionsCtrl.alert!=null" type="{{reductionsCtrl.alert.type}}" class="alert-success" dismiss-on-timeout="2000" close="reductionsCtrl.closeAlertSaved()"><?php _e( 'saved_alert_title', 'resa' ) ?></div>
		<div class="col-md-12">
			<div class="btn-group">
				<button type="button" class="btn btn-default resa_btn" ng-click="reductionsCtrl.addReduction()"><?php _e('add_reduction_link_title', 'resa'); ?></button>
			</div>
		</div>
		<div class="col-md-12 resa_lang" ng-if="reductionsCtrl.settings.languages.length > 1">
			<span ng-class="{'active_lang': reductionsCtrl.currentLanguage == language}" ng-click="reductionsCtrl.setCurrentLanguage(language)" ng-repeat="language in reductionsCtrl.settings.languages">
				<img ng-src="<?php echo plugin_dir_url(__FILE__).'../images/flags/'; ?>{{ language }}.png" />
			</span>
		</div>
	</div>

	<div class="panel-group" id="resa_service_panel">
		<div class="panel panel-default resa_panel" ng-repeat="reduction in reductionsCtrl.reductions track by $index">
			<div class="panel-heading">
				<h4 class="panel-title">
					<!-- <div class="resa_accordion_header_icon_move">
						<a href=""><span class="glyphicon glyphicon-arrow-up"></span></a>
						<a href=""><span class="glyphicon glyphicon-arrow-down"></span></a>
					</div>
					//-->
					<a ng-click="reduction.opened=!reduction.opened"  data-toggle="collapse" data-parent="#resa_service_panel" href="#collapse{{$index}}">{{ reductionsCtrl.getTitle(reduction.name[reductionsCtrl.currentLanguage], '<?php _e('to_define_words', 'resa'); ?>') }}<span ng-if="reductionsCtrl.getVoucher(reduction).length > 0"> ----- {{ reductionsCtrl.getVoucherLink(reduction) }}</span>
					</a>
					<div class="resa_accordion_header_icon_bloc">
						<div class="resa_accordion_header_icon">
							<a ng-click="reduction.opened=!reduction.opened" data-toggle="collapse" data-parent="#resa_service_panel" href="#collapse{{$index}}"><span class="glyphicon" ng-class="{'glyphicon-minus': reduction.opened, 'glyphicon-plus': !reduction.opened}"></span></a>
						</div>
						<div class="resa_accordion_header_icon">
							<a ng-click="reductionsCtrl.duplicateReduction(reduction)"><span class="glyphicon glyphicon-duplicate"></span></a>
							<a ng-click="reductionsCtrl.removeReduction($index, {
								title: '<?php _e('ask_delete_reduction_text_title_dialog','resa') ?>',
								text: '<?php _e('ask_delete_reduction_text_dialog','resa') ?>',
								confirmButton: '<?php _e('ask_delete_reduction_confirmButton_dialog','resa') ?>',
								cancelButton: '<?php _e('ask_delete_reduction_cancelButton_dialog','resa') ?>'
							})"><span class="glyphicon glyphicon-trash"></span></a>
						</div>
						<div class="resa_accordion_header_icon">
							<label>
								<input class="control-label" type="radio"  ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reduction.visibility" value="0"> Tous
							</label>
							<label>
								<input class="control-label" type="radio"  ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reduction.visibility" value="1"> Administration
							</label>
							<label>
								<input class="control-label" type="radio"  ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reduction.visibility" value="2"> Formulaire
							</label>
							<label>
								<input class="control-label" type="checkbox"  ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reduction.activated">  <?php _e( 'activated_checkbox_title', 'resa' ) ?>
							</label>
						</div>
					</div>
				</h4>
			</div>
			<div id="collapse{{$index}}" class="panel-collapse collapse">
				<div class="panel-body resa_panel_body">
					<div class="panel-group" id="resa_service_subpanel">
						<div class="panel panel-default resa_panel">
							<div class="panel-heading">
								<h4 class="panel-title"> <a data-toggle="collapse" data-parent="#resa_sercive_subpanel" href="#collapse{{$index}}_1">  <?php _e('general_title', 'resa'); ?> </a> </h4>
							</div>
							<div id="collapse{{$index}}_1" class="panel-collapse collapse">
								<div class="panel-body">
									<div class="container resa_container">
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label class="control-label" for="formInput5"><?php _e('name_field_title', 'resa') ?></label>
													<input type="text" class="form-control" id="formInput5" placeholder="<?php _e('name_field_title', 'resa') ?>" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reduction.name[reductionsCtrl.currentLanguage]" >
												</div>
											</div>
										</div>
										<div class="row" ng-if="reductionsCtrl.getVoucherLink(reduction).length > 0">
											<div class="col-md-12">
												<div class="form-group">
													<label class="control-label">Lien coupon : </label>
													<span>{{ reductionsCtrl.getVoucherLink(reduction) }}</span>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label class="control-label" for="formInput9"><?php _e('presentation_field_title', 'resa') ?> </label>   <textarea class="form-control" rows="3" id="formInput9" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reduction.presentation[reductionsCtrl.currentLanguage]"></textarea>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="panel panel-default resa_panel">
							<div class="panel-heading">
								<h4 class="panel-title"> <a data-toggle="collapse" data-parent="#resa_sercive_subpanel" href="#collapse{{$index}}_2">  <?php _e('conditions_title', 'resa'); ?> </a> </h4>
							</div>
							<div id="collapse{{$index}}_2" class="panel-collapse collapse">
								<div class="panel-body">
									<p><?php _e('conditions_description_reduction', 'resa'); ?></p>
									<div class="container resa_container"></div>
									<div class="container resa_container">
										<div class="row resa_rule_bloc" ng-repeat="reductionConditions in reduction.reductionConditionsList track by $index">
											<div class="col-md-12 col-sm-12 col-xs-12">
												<h4>
													<div class="resa_accordion_header_icon">
														<a ng-click="reductionsCtrl.duplicateReductionConditions(reduction, reductionConditions); reductionsCtrl.reductionUpdated(reduction);"><span class="glyphicon glyphicon-duplicate"></span></a>
														<a ng-click="reductionsCtrl.deleteReductionConditions(reduction, $index); reductionsCtrl.reductionUpdated(reduction);"><span class="glyphicon glyphicon-trash"></span></a>
													</div>
													<?php _e('Condition_word', 'resa'); ?> {{ $index + 1 }}
												</h4>
											</div>
											<div class="col-md-12">
												<div class="form-group">
													<label class="control-label" for="formInput15"><?php _e('if_on_words_reductions', 'resa') ?></label>
													<select  id="formInput15" class="form-control" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-options="type.id as type.title for type in reductionsCtrl.typeCheckReductionConditions" ng-model="reductionConditions.type"></select>
													<label>
														<input class="control-label" type="checkbox" ng-change="reductionsCtrl.reductionUpdated(reduction)" type="checkbox" ng-model="reductionConditions.merge"> <?php _e('merge_appointments_field_title', 'resa'); ?>
													</label>
												</div>
											</div>
											<div class="col-md-12">
												<h4><?php _e('there_are_words', 'resa') ?></h4>
											</div>
											<span ng-repeat="reductionCondition in reductionConditions.reductionConditions track by $index">
												<div class="col-md-12" ng-if="$index > 0">
													<h4><?php _e('and_word', 'resa') ?></h4>
												</div>
												<div class="col-md-12" >
													<div class="clear resa_section_1">
														<div class="col-md-12 col-sm-12 col-xs-12">
															<div class="col-md-2 reduction_column_action text-center">
																<a ng-click="reductionsCtrl.duplicateReductionCondition(reductionConditions, reductionCondition); reductionsCtrl.reductionUpdated(reduction);"><span class="glyphicon glyphicon-duplicate"></span></a>
																<a ng-click="reductionsCtrl.deleteReductionCondition(reductionConditions, $index); reductionsCtrl.reductionUpdated(reduction);"><span class="glyphicon glyphicon-trash"></span></a>
															</div>
															<div class="col-md-10">
																<select class="form-control"  ng-change="reductionsCtrl.reductionUpdated(reduction); reductionsCtrl.reinitReduction(reductionCondition)" ng-options="type.id as type.title for type in reductionsCtrl.typeReductionConditions[reductionConditions.type]" ng-model="reductionCondition.type"></select>
															</div>
														</div>
														<div class="col-md-12 col-sm-12 col-xs-12">
															<div class="container resa_container">
																<div class="row reduction_sub_row" ng-if="reductionCondition.type == 'code'">
																	<div class="col-xs-12 col-md-6 col-sm-6">
																		<select class="form-control" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reductionCondition.param1">
																			<option value="0"><?php _e('equals_to_title', 'resa')?></option>
																			<option value="1"><?php _e('not_equals_to_title', 'resa')?></option>
																		</select>
																	</div>
																	<div class="col-xs-12 col-md-6 col-sm-6">
																		<input ng-change="reductionsCtrl.reductionUpdated(reduction)" type="text" ng-model="reductionCondition.param2" placeholder="<?php _e('code_here_sentence', 'resa'); ?>" />
																	</div>
																	<div class="col-xs-12 col-md-12 col-sm-12">
																		<label>
																			<input class="control-label"  ng-change="reductionsCtrl.reductionUpdated(reduction)" type="checkbox" ng-model="reductionCondition.param3" /><?php _e('maximum_usage_checkbox_title', 'resa') ?>
																		</label>
																	</div>
																	<div class="col-xs-12 col-md-12 col-sm-12">
																		<input type="number" class="form-control" placeholder="0" ng-disabled="!reductionCondition.param3" ng-model="reductionCondition.param4" />
																	</div>
																</div>
																<div class="row reduction_sub_row" ng-if="reductionCondition.type == 'services'">
																	<div class="row" ng-repeat="reductionConditionService in reductionCondition.reductionConditionServices">
																		<div class="col-xs-12 col-md-1 col-sm-1">
																			<button type="button" class="btn btn-default resa_btn"  ng-click="reductionsCtrl.deleteReductionConditionService(reductionCondition, $index); reductionsCtrl.reductionUpdated(reduction);"><span class="glyphicon glyphicon-trash"></span></button>
																		</div>
																		<div class="col-xs-12 col-md-3 col-sm-3">
																			<select ng-show="reductionConditionService.idService > 0" class="form-control" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reductionConditionService.method">
																				<option value="0" ng-selected="reductionConditionService.method == 0"><?php _e('equals_to_title', 'resa')?></option>
																				<option value="1" ng-selected="reductionConditionService.method == 1"><?php _e('not_equals_to_title', 'resa')?></option>
																			</select>
																		</div>
																		<div class="col-xs-12 col-md-4 col-sm-4">
																			<select class="form-control" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-options="service.id as reductionsCtrl.getTextByLocale(service.name, reductionsCtrl.currentLanguage) for service in reductionsCtrl.services" ng-model="reductionConditionService.idService">
																			</select>
																		</div>
																		<div class="col-xs-12 col-md-4 col-sm-4">
																			<div class="checkbox">
																				<label class="control-label">
																					<input type="checkbox" ng-click="reductionConditionService.priceList = []; reductionsCtrl.reductionUpdated(reduction);" ng-checked="reductionConditionService.priceList.length == 0" /><?php _e('all_prices_list_checkbox_title', 'resa'); ?>
																				</label>
																				<br />
																				<span ng-repeat="price in reductionsCtrl.getPricesOfService(reductionConditionService.idService)">
																					<label class="control-label">
																						<input ng-change="reductionsCtrl.reductionUpdated(reduction)" type="checkbox" ng-model="reductionConditionService.priceList[$index]" ng-true-value="{{ price.id }}" ng-false-value="-1" /> {{ reductionsCtrl.getTextByLocale(price.name, reductionsCtrl.currentLanguage) }}
																					</label>
																					<br />
																				</span>
																			</div>
																		</div>
																		<div class="col-xs-12 col-md-3 col-sm-3 text-right">
																			<p><?php _e('and_quantity_words', 'resa')?></p>
																		</div>
																		<div class="col-xs-12 col-md-4 col-sm-4">
																			<select class="form-control" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reductionConditionService.methodQuantity">
																				<option value="0" ng-selected="reductionConditionService.methodQuantity == 0"><?php _e('more_or_equals_to_title', 'resa')?></option>
																				<option value="1" ng-selected="reductionConditionService.methodQuantity == 1"><?php _e('equals_to_title', 'resa')?></option>
																				<option value="2" ng-selected="reductionConditionService.methodQuantity == 2"><?php _e('less_than_title', 'resa')?></option>
																				<option value="3" ng-selected="reductionConditionService.methodQuantity == 3"><?php _e('bracket_title', 'resa')?></option>
																				<option value="4" ng-selected="reductionConditionService.methodQuantity == 4">Entre >= "min" et <= "max"</option>
																			</select>
																		</div>
																		<div class="col-xs-12 col-md-4 col-sm-4">
																			<input type="number" class="form-control" placeholder="0" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reductionConditionService.number" />
																			<input ng-if="reductionConditionService.methodQuantity == 4" type="number" class="form-control" placeholder="0" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reductionConditionService.number2" />
																		</div>
																		<div class="row" ng-repeat="date in reductionConditionService.dates">
																			<div class="col-xs-12 col-md-1 col-sm-1">
																				<button type="button" class="btn btn-default resa_btn"  ng-click="reductionsCtrl.deleteReductionConditionServiceDate(reductionConditionService, $index); reductionsCtrl.reductionUpdated(reduction);"><span class="glyphicon glyphicon-trash"></span></button>
																			</div>
																			<div class="col-xs-12 col-md-1 col-sm-1">
																				<?php _e('Date_word', 'resa') ?>:
																			</div>
																			<div class="col-xs-12 col-md-4 col-sm-4">
																				<select class="form-control" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="date.method">
																					<option value="0"><?php _e('before_to_title', 'resa')?></option>
																					<option value="1"><?php _e('equals_to_title', 'resa')?></option>
																					<option value="2"><?php _e('after_to_title', 'resa')?></option>
																				</select>
																			</div>
																			<div class="col-xs-12 col-md-6 col-sm-6">
																				<input uib-datepicker-popup ng-click="reductionsCtrl.popupDate[$parent.$index][$index]=true" is-open="reductionsCtrl.popupDate[$parent.$index][$index]" datepicker-options="reductionsCtrl.dateOptions" type="date" class="form-control" placeholder="0" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="date.date" close-text="<?php _e('Close_word', 'resa') ?>" clear-text="<?php _e('Clear_word', 'resa') ?>" current-text="<?php _e('Today_word', 'resa') ?>">
																			</div>
																		</div>
																		<div class="row" ng-repeat="time in reductionConditionService.times">
																			<div class="col-xs-12 col-md-1 col-sm-1">
																				<button type="button" class="btn btn-default resa_btn"  ng-click="reductionsCtrl.deleteReductionConditionServiceTime(reductionConditionService, $index); reductionsCtrl.reductionUpdated(reduction);"><span class="glyphicon glyphicon-trash"></span></button>
																			</div>
																			<div class="col-xs-12 col-md-1 col-sm-1">
																				<?php _e('Time_word', 'resa') ?>:
																			</div>
																			<div class="col-xs-12 col-md-4 col-sm-4">
																				<select class="form-control" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="time.method">
																					<option value="0"><?php _e('before_to_title', 'resa')?></option>
																					<option value="1"><?php _e('equals_to_title', 'resa')?></option>
																					<option value="2"><?php _e('after_to_title', 'resa')?></option>
																				</select>
																			</div>
																			<div class="col-xs-12 col-md-6 col-sm-6">
																				<time-picker on-change="reductionsCtrl.reductionUpdated(reduction)"  time="time.time" template-url="'<?php echo plugin_dir_url(__FILE__ ).'/RESA_timepicker.html'; ?>'"></time-picker>
																			</div>
																		</div>
																		<div class="row" ng-show="reductionConditionService.days.length > 0">
																			<div class="col-xs-12 col-md-1 col-sm-1">
																				<button type="button" class="btn btn-default resa_btn"  ng-click="reductionsCtrl.deleteReductionConditionServiceDays(reductionConditionService, $index); reductionsCtrl.reductionUpdated(reduction);"><span class="glyphicon glyphicon-trash"></span></button>
																			</div>
																			<div class="col-xs-12 col-md-1 col-sm-1">
																				<?php _e('days_field_title', 'resa') ?>:
																			</div>
																			<div class="col-xs-12 col-md-6 col-sm-6">
																				<span ng-repeat="day in reductionsCtrl.days">
																					<label class="control-label">
																					<input ng-change="reductionsCtrl.reductionUpdated(reduction)" type="checkbox" ng-model="reductionConditionService.days[$index]" ng-true-value="{{ $index }}" ng-false-value="-1" /> {{ day }}
																					</label>
																					<br />
																				</span>
																			</div>
																		</div>
																		<div class="col-md-12">
																			<div class="row">
																				<div class="col-xs-2">
																					<button type="button" class="btn btn-default resa_btn"  ng-click="reductionsCtrl.addReductionConditionServiceDate(reductionConditionService); reductionsCtrl.reductionUpdated(reduction);"><?php _e('add_date_link_title', 'resa') ?></button>
																				</div>
																				<div class="col-xs-2">
																					<button type="button" class="btn btn-default resa_btn"  ng-click="reductionsCtrl.addReductionConditionServiceTime(reductionConditionService); reductionsCtrl.reductionUpdated(reduction);"><?php _e('add_time_link_title', 'resa') ?></button>
																				</div>
																				<div class="col-xs-2">
																					<button ng-show="reductionConditionService.days.length == 0" type="button" class="btn btn-default resa_btn"  ng-click="reductionsCtrl.addReductionConditionServiceDays(reductionConditionService); reductionsCtrl.reductionUpdated(reduction);"><?php _e('add_days_link_title', 'resa') ?></button>
																				</div>
																			</div>
																		</div>
																	</div>
																	<div class="col-md-12">
																		<button type="button" class="btn btn-default resa_btn"  ng-click="reductionsCtrl.addReductionConditionService(reductionCondition); reductionsCtrl.reductionUpdated(reduction);"><?php _e('add_service_link_title', 'resa') ?></button>
																	</div>
																	<div class="col-xs-12 col-md-6 col-sm-6">
																		<select class="form-control" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reductionCondition.param1">
																			<option value="0"><?php _e('custom_quantity_to_title', 'resa')?></option>
																			<option value="1"><?php _e('added_more_or_equals_to_title', 'resa')?></option>
																			<option value="2"><?php _e('mutual_more_or_equals_to_title', 'resa')?></option>
																			<option value="3"><?php _e('mutual_bracket_title', 'resa')?></option>
																		</select>
																	</div>
																	<div class="col-xs-12 col-md-6 col-sm-6">
																		<input type="number" class="form-control" placeholder="0" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reductionCondition.param2" />
																	</div>
																</div>
																<div class="row reduction_sub_row" ng-if="reductionCondition.type == 'amount'">
																	<div class="col-xs-12 col-md-6 col-sm-6">
																		<select class="form-control" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reductionCondition.param1">
																			<option value="0"><?php _e('more_or_equals_to_title', 'resa')?></option>
																			<option value="1"><?php _e('equals_to_title', 'resa')?></option>
																			<option value="2"><?php _e('less_than_title', 'resa')?></option>
																		</select>
																	</div>
																	<div class="col-xs-12 col-md-6 col-sm-6">
																		<input type="number" class="form-control" placeholder="0" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reductionCondition.param2" />
																	</div>
																</div>
																<div class="row reduction_sub_row" ng-if="reductionCondition.type == 'registerDate'">
																	<div class="col-xs-12 col-md-6 col-sm-6">
																		<select class="form-control" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reductionCondition.param1">
																			<option value="0"><?php _e('before_to_title', 'resa')?></option>
																			<option value="1"><?php _e('equals_to_title', 'resa')?></option>
																			<option value="2"><?php _e('after_to_title', 'resa')?></option>
																		</select>
																	</div>
																	<div class="col-xs-12 col-md-6 col-sm-6">
																		<input uib-datepicker-popup  ng-click="reductionsCtrl.popupDate[$parent.$index][$index]=true" is-open="reductionsCtrl.popupDate[$parent.$index][$index]" datepicker-options="reductionsCtrl.dateOptions" type="date" class="form-control" placeholder="0" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reductionCondition.param2" close-text="<?php _e('Close_word', 'resa') ?>" clear-text="<?php _e('Clear_word', 'resa') ?>" current-text="<?php _e('Today_word', 'resa') ?>">
																	</div>
																	<div class="col-xs-12 col-md-12 col-sm-12 text-center">
																		<time-picker on-change="reductionsCtrl.reductionUpdated(reduction)" time="reductionCondition.param3" template-url="'<?php echo plugin_dir_url(__FILE__ ).'/RESA_timepicker.html'; ?>'"></time-picker>
																	</div>
																</div>
																<div class="row reduction_sub_row" ng-if="reductionCondition.type == 'customer'">
																	<div class="col-xs-12 col-md-6 col-sm-6">
																		<!--
																		<select class="form-control" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reductionCondition.param1">
																			<option value="0"><?php _e('for_company_account_title', 'resa')?></option>
																			<option value="1"><?php _e('not_for_company_account_title', 'resa')?></option>
																		</select>
																		//-->
																		<select ng-model="reductionCondition.param1" ng-options="typeAccount.id as reductionsCtrl.getTextByLocale(typeAccount.name) for typeAccount in reductionsCtrl.settings.typesAccounts"></select>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</span>
											<div class="col-md-12">
												<button type="button" class="btn btn-default resa_btn"  ng-click="reductionsCtrl.addReductionCondition(reductionConditions); reductionsCtrl.reductionUpdated(reduction);"><?php _e('And_word', 'resa') ?></button>
											</div>
										</div>
										<button type="button" class="btn btn-default resa_btn" ng-click="reductionsCtrl.addReductionConditions(reduction); reductionsCtrl.reductionUpdated(reduction);"><?php _e('add_conditions_link_title', 'resa'); ?></button>
									</div>
								</div>
							</div>
						</div>
						<div class="panel panel-default resa_panel">
							<div class="panel-heading">
								<h4 class="panel-title"> <a data-toggle="collapse" data-parent="#resa_sercive_subpanel" href="#collapse{{ reduction.id }}_3"><?php _e('reduction_applications_title', 'resa'); ?></a> </h4>
							</div>
							<div id="collapse{{ reduction.id }}_3" class="panel-collapse collapse">
								<div class="panel-body">
									<p><?php _e('reduction_applications_description', 'resa') ?></p>
									<div class="container resa_container">
										<div class="row resa_rule_bloc" ng-repeat="reductionApplication in reduction.reductionApplications track by $index">
											<div class="col-md-12 col-sm-12 col-xs-12">
												<h4>
													<div class="resa_accordion_header_icon">
														<a ng-click="reductionsCtrl.duplicateReductionApplication(reduction, reductionApplication); reductionsCtrl.reductionUpdated(reduction);"><span class="glyphicon glyphicon-duplicate"></span></a>
														<a ng-click="reductionsCtrl.deleteReductionApplication(reduction, $index); reductionsCtrl.reductionUpdated(reduction);"><span class="glyphicon glyphicon-trash"></span></a>
													</div>
													<?php _e('reduction_applications_title', 'resa'); ?> {{ $index + 1 }}
												</h4>
											</div>
											<div class="col-md-6 col-sm-8">
												<div class="form-group">
													<label class="control-label" for="formInput15"><?php _e('Reduction_word', 'resa'); ?></label>
													<select id="formInput15" class="form-control" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-options="typeReductionApplication.id as typeReductionApplication.title for typeReductionApplication in reductionsCtrl.typeReductionApplications" ng-model="reductionApplication.type"></select>
												</div>
											</div>
											<div class="col-md-4 col-sm-4">
												<div class="form-group">
													<label class="control-label" for="formInput16"><?php _e('value_word', 'resa'); ?> </label>
													<textarea class="form-control" id="formInput16" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reductionApplication.value" ng-if="reductionApplication.type == 5"></textarea>
													<input ng-change="reductionsCtrl.reductionUpdated(reduction)"  id="formInput16"  class="form-control" type="number" ng-model="reductionApplication.value" ng-if="reductionApplication.type != 5" placeholder="0" />
												</div>
											</div>
											<div class="col-md-2 col-sm-4" style="vertical-align: middle;">
												<div class="checkbox">
													<label class="control-label">
														<input type="checkbox" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reductionApplication.onlyOne" /> La lever qu'une seule fois !
													</label>
												</div>
											</div>
											<div class="col-md-12">
												<div class="form-group">
													<label class="control-label" for="formInput16"><?php _e('apply_reduction_title', 'resa'); ?></label>
													<select class="form-control" id="formInput16" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-options="typeReductionApplication.id as typeReductionApplication.title for typeReductionApplication in reductionsCtrl.typeApplicationsTypeReduction[reductionApplication.type]" ng-model="reductionApplication.applicationType"></select>
												</div>
											</div>
											<span ng-if="reductionApplication.applicationType == 2">
												<div class="col-md-12">
													<div class="form-group">
														<label class="control-label" for="formInput17"><?php _e('if_on_words_reductions', 'resa'); ?></label>
														 <select id="formInput17" class="form-control" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-options="typeApplicationConditionOn.id as typeApplicationConditionOn.title for typeApplicationConditionOn in reductionsCtrl.typeApplicationConditionsOn" ng-model="reductionApplication.applicationTypeOn"></select>
													</div>
												</div>
												<span ng-repeat="reductionConditionsApplication in reductionApplication.reductionConditionsApplicationList">
													<div class="col-md-12">
														<h4>
															<span ng-if="$index > 0"><?php _e('Or_word', 'resa') ?>, </span><?php _e('there_are_words', 'resa')?>
														</h4>
														<a ng-click="reductionsCtrl.deleteReductionConditionsApplication(reductionApplication, $index); reductionsCtrl.reductionUpdated(reduction);"><?php _e('remove_case_link_title', 'resa'); ?></a>
													</div>
													<span ng-repeat="reductionConditionApplication in reductionConditionsApplication.reductionConditionsApplications">
														<div class="col-md-12" ng-if="$index > 0">
															<h4><?php _e('and_word', 'resa') ?></h4>
														</div>
														<div class="col-md-12" >
															<div class="clear resa_section_1">
																<div class="col-md-12 col-sm-12 col-xs-12">
																	<div class="col-md-2 reduction_column_action text-center">
																		<!-- <a href=""><span class="glyphicon glyphicon-duplicate"></span></a> //-->
																		<a ng-click="reductionsCtrl.deleteReductionConditionApplication(reductionConditionsApplication, $index); reductionsCtrl.reductionUpdated(reduction);"><span class="glyphicon glyphicon-trash"></span></a>
																	</div>
																	<div class="col-md-10">
																		<select class="form-control" ng-change="reductionsCtrl.reductionUpdated(reduction); reductionsCtrl.reinitReduction(reductionConditionApplication)" ng-options="type.id as type.title for type in reductionsCtrl.typeReductionApplicationConditions" ng-model="reductionConditionApplication.type"></select>
																	</div>
																</div>
																<div class="col-md-12 col-sm-12 col-xs-12">
																	<div class="container resa_container">
																		<div class="row reduction_sub_row" ng-if="reductionConditionApplication.type == 'service'">
																			<div class="col-xs-12 col-md-4 col-sm-4">
																				<select class="form-control" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reductionConditionApplication.param1">
																				<option value="0"><?php _e('equals_to_title', 'resa')?></option>
																				<option value="1"><?php _e('not_equals_to_title', 'resa')?></option>
																				</select>
																			</div>
																			<div class="col-xs-12 col-md-4 col-sm-4">
																				<select class="form-control" ng-options="service.id+'' as reductionsCtrl.getTextByLocale(service.name, reductionsCtrl.currentLanguage) for service in reductionsCtrl.services" ng-model="reductionConditionApplication.param2"></select>
																			</div>
																			<div class="col-xs-12 col-md-4 col-sm-4">
																				<div class="checkbox">
																					<label class="control-label">
																						<input type="checkbox" ng-click="reductionConditionApplication.param3 = []; reductionsCtrl.reductionUpdated(reduction);" ng-checked="reductionConditionApplication.param3.length == 0" /><?php _e('all_prices_list_checkbox_title', 'resa'); ?>
																					</label>
																					<br />
																					<span ng-repeat="price in reductionsCtrl.getPricesOfService(reductionConditionApplication.param2 * 1)">
																						<label class="control-label">
																						<input ng-change="reductionsCtrl.reductionUpdated(reduction)" type="checkbox" ng-model="reductionConditionApplication.param3[$index]" ng-true-value="{{ price.id }}" ng-false-value="-1" /> {{ reductionsCtrl.getTextByLocale(price.name, reductionsCtrl.currentLanguage) }}
																						</label>
																						<br />
																					</span>
																				</div>
																			</div>
																			<div class="col-xs-12 col-md-4 col-sm-4 text-right">
																				<p><?php _e('and_quantity_words', 'resa')?></p>
																			</div>
																			<div class="col-xs-12 col-md-4 col-sm-4">
																				<select class="form-control" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reductionConditionApplication.param4">
																					<option value="0"><?php _e('more_or_equals_to_title', 'resa')?></option>
																					<option value="1"><?php _e('less_than_title', 'resa')?></option>
																					<option value="2"><?php _e('bracket_title', 'resa')?></option>
																				</select>
																			</div>
																			<div class="col-xs-12 col-md-4 col-sm-4">
																				<input type="number" class="form-control" placeholder="0" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reductionConditionApplication.param5" />
																			</div>
																		</div>
																		<div class="row reduction_sub_row" ng-if="reductionConditionApplication.type == 'amount'">
																			<div class="col-xs-12 col-md-6 col-sm-6">
																				<select class="form-control" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reductionConditionApplication.param1">
																					<option value="0"><?php _e('more_or_equals_to_title', 'resa')?></option>
																					<option value="1"><?php _e('less_than_title', 'resa')?></option>
																				</select>
																			</div>
																			<div class="col-xs-12 col-md-6 col-sm-6">
																				<input type="number" class="form-control" placeholder="0" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reductionConditionApplication.param2" />
																			</div>
																		</div>
																		<div class="row reduction_sub_row" ng-if="reductionConditionApplication.type == 'date'">
																			<div class="col-xs-12 col-md-6 col-sm-6">
																				<select class="form-control" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reductionConditionApplication.param1">
																					<option value="0"><?php _e('before_to_title', 'resa')?></option>
																					<option value="1"><?php _e('equals_to_title', 'resa')?></option>
																					<option value="2"><?php _e('after_to_title', 'resa')?></option>
																				</select>
																			</div>
																			<div class="col-xs-12 col-md-6 col-sm-6">
																				<input uib-datepicker-popup class="form-control" type="date" ng-click="reductionsCtrl.popupDateApplication[$parent.$index][$index] = true" ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reductionConditionApplication.param2" is-open="reductionsCtrl.popupDateApplication[$parent.$index][$index]" datepicker-options="reductionsCtrl.dateOptions" ng-required="true" close-text="<?php _e('Close_word', 'resa') ?>" clear-text="<?php _e('Clear_word', 'resa') ?>" current-text="<?php _e('Today_word', 'resa') ?>">
																			</div>
																		</div>
																		<div class="row reduction_sub_row" ng-if="reductionConditionApplication.type == 'time'">
																			<div class="col-xs-12 col-md-12 col-sm-12">
																				<select ng-change="reductionsCtrl.reductionUpdated(reduction)" ng-model="reductionConditionApplication.param1">
																					<option value="0"><?php _e('begin_before_title', 'resa')?></option>
																					<option value="1"><?php _e('after_before_title', 'resa')?></option>
																				</select>
																			</div>
																			<div class="col-xs-12 col-md-12 col-sm-12 text-center">
																				<time-picker on-change="reductionsCtrl.reductionUpdated(reduction)" time="reductionConditionApplication.param2" template-url="'<?php echo plugin_dir_url(__FILE__ ).'/RESA_timepicker.html'; ?>'"></time-picker>
																			</div>
																		</div>
																		<div class="row reduction_sub_row" ng-if="reductionConditionApplication.type == 'days'">
																			<div class="col-xs-12 col-md-6 col-sm-6">
																				<span ng-repeat="day in reductionsCtrl.days">
																					<label class="control-label">
																					<input ng-change="reductionsCtrl.reductionUpdated(reduction)" type="checkbox" ng-model="reductionConditionApplication.param3[$index]" ng-true-value=" {{ $index }}" ng-false-value="-1" /> {{ day }}
																					</label>
																					<br />
																				</span>
																			</div>
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</span>
													<div class="col-md-12">
														<button type="button" class="btn btn-default resa_btn" ng-click="reductionsCtrl.addReductionConditionApplication(reductionConditionsApplication, $index); reductionsCtrl.reductionUpdated(reduction);"><?php _e('And_word', 'resa') ?></button>
													</div>
												</span>
												<div class="col-md-12">
													<button type="button" class="btn btn-default resa_btn" ng-click="reductionsCtrl.addReductionConditionsApplication(reductionApplication); reductionsCtrl.reductionUpdated(reduction);"><?php _e('Or_word', 'resa') ?>, <?php _e('there_are_words', 'resa') ?></button>
												</div>
												<?php
												/*  TODO
												<div class="col-md-12">
													<h4>Récapitulatif de l'application</h4>
													<p>Appliquer une réduction <span class="reduction_recap_span">{{ reductionsCtrl.getTypeReductionApplications(reductionApplication).title }}</span> d'une valeur de <span class="reduction_recap_span">0</span> <span class="reduction_recap_span">sur le cas suivant :</span> </p>
													<p>Si sur <span class="reduction_recap_span">une même date</span> il y a : </p>
													<ul>
														<li>Service est égal à service 1 tarif 1, tarif 2 et la quantité selectionné est supérieure ou égale à 0</li>
														<li>Montant de la réservation est supérieur ou égal à 0</li>
														<li>Date du creneau avant le mm/dd/2016</li>
														<li>Heure du creneau débute avant 0h0min</li>
													</ul>
													<p>Ou, si il y a :</p>
													<ul>
													<li>Heure du creneau débute avant 0h0min</li>
													</ul>
												</div>
												*/
												?>
											</span>
										</div>
										<button type="button" class="btn btn-default resa_btn" ng-click="reductionsCtrl.addReductionApplication(reduction); reductionsCtrl.reductionUpdated(reduction);"><?php _e('add_application_link_title', 'resa'); ?></button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<button type="button" class="btn btn-default resa_btn" ng-click="reductionsCtrl.updateReductions()"><?php _e('update_reductions_link_title', 'resa'); ?></button>
	<div class="resa_save_block">
		<button ng-click="reductionsCtrl.addReduction()" type="button" class="btn btn-default resa_main_btn">
			<span class="glyphicon glyphicon-plus"></span>
			<span class="resa_main_btn_title"><?php _e('add_reduction_link_title', 'resa'); ?></span>
		</button>
		<button ng-click="reductionsCtrl.updateReductions()" type="button" class="btn btn-default resa_main_btn">
			<span class="glyphicon glyphicon-floppy-save"></span>
			<span class="resa_main_btn_title"><?php _e('update_reductions_link_title', 'resa'); ?></span>
		</button>
	</div>
</div>
