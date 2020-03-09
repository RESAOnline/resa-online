import { NgModule }             from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

import { ActivitiesComponent } from '../pages/activities/activities';
import { ActivityComponent } from '../pages/activity/activity';
import { FormsComponent } from '../pages/forms/forms';
import { MembersComponent } from '../pages/members/members';
import { ReductionsComponent } from '../pages/reductions/reductions';
import { EquipmentsComponent } from '../pages/equipments/equipments';
import { GroupsComponent } from '../pages/groups/groups';
import { NotificationsComponent } from '../pages/notifications/notifications';
import { SettingsComponent } from '../pages/settings/settings';
import { CalendarsComponent } from '../pages/calendars/calendars';
import { DailyComponent } from '../pages/daily/daily';
import { VouchersComponent } from '../pages/vouchers/vouchers';
import { MembersStatsComponent } from '../pages/membersStats/membersStats';
import { StatisticsActivitiesComponent } from '../pages/statisticsActivities/statisticsActivities';
import { StatisticsBookingsComponent } from '../pages/statisticsBookings/statisticsBookings';
import { InstallationComponent } from '../pages/installation/installation';
import { PlanningComponent } from '../pages/planning/planning';
import { PlanningMembersComponent } from '../pages/planningMembers/planningMembers';
import { PlanningGroupsComponent } from '../pages/planningGroups/planningGroups';
import { BookingsListComponent } from '../pages/bookingsList/bookingsList';
import { QuotationsListComponent } from '../pages/quotationsList/quotationsList';
import { BookingsCalendarComponent } from '../pages/bookingsCalendar/bookingsCalendar';
import { BookingsDetailsComponent } from '../pages/bookingsDetails/bookingsDetails';
import { CustomersComponent } from '../pages/customers/customers';
import { CustomerComponent } from '../pages/customer/customer';

import { CanDeactivateGuard } from '../others/CanDeactivateGuard';


const routes: Routes = [
  { path: 'activities', component: ActivitiesComponent, canDeactivate: [CanDeactivateGuard] },
  { path: 'activity/:id', component: ActivityComponent, canDeactivate: [CanDeactivateGuard] },
  { path: 'activity/:id/:tab', component: ActivityComponent, canDeactivate: [CanDeactivateGuard] },
  { path: 'activity', component: ActivityComponent, canDeactivate: [CanDeactivateGuard] },
  { path: 'forms', component: FormsComponent, canDeactivate: [CanDeactivateGuard] },
  { path: 'forms/:idForm', component: FormsComponent, canDeactivate: [CanDeactivateGuard] },
  { path: 'members', component: MembersComponent, canDeactivate: [CanDeactivateGuard] },
  { path: 'equipments', component: EquipmentsComponent, canDeactivate: [CanDeactivateGuard] },
  { path: 'reductions', component: ReductionsComponent, canDeactivate: [CanDeactivateGuard] },
  { path: 'groups', component: GroupsComponent, canDeactivate: [CanDeactivateGuard] },
  { path: 'notifications', component: NotificationsComponent, canDeactivate: [CanDeactivateGuard] },
  { path: 'settings', component: SettingsComponent, canDeactivate: [CanDeactivateGuard] },
  { path: 'settings/:tab', component: SettingsComponent, canDeactivate: [CanDeactivateGuard] },
  { path: 'calendars', component: CalendarsComponent, canDeactivate: [CanDeactivateGuard] },
  { path: 'daily', component: DailyComponent },
  { path: 'vouchers', component: VouchersComponent },
  { path: 'vouchers/:voucher', component: VouchersComponent },
  { path: 'statisticsMembers', component: MembersStatsComponent },
  { path: 'statisticsActivities', component: StatisticsActivitiesComponent },
  { path: 'statisticsBookings', component: StatisticsBookingsComponent },
  { path: 'installation', component: InstallationComponent },
  { path: 'planningBookings', component: PlanningComponent },
  { path: 'planningMembers', component: PlanningMembersComponent },
  { path: 'planningGroups', component: PlanningGroupsComponent },
  { path: 'bookingsList', component: BookingsListComponent },
  { path: 'bookingsCalendar', component: BookingsCalendarComponent },
  { path: 'bookingsDetails', component: BookingsDetailsComponent },
  { path: 'quotations', component: QuotationsListComponent },
  { path: 'customers', component: CustomersComponent },
  { path: 'customer/:id', component: CustomerComponent },
  { path: '', component: BookingsCalendarComponent, canDeactivate: [CanDeactivateGuard] }
];

@NgModule({
  imports: [ RouterModule.forRoot(routes) ],
  exports: [ RouterModule ]
})

export class AppRoutingModule {}
