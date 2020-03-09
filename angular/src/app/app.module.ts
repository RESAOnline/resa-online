import { BrowserModule } from '@angular/platform-browser';
import { FormsModule } from '@angular/forms';
import { NgModule } from '@angular/core';
import { HttpModule }    from '@angular/http';
import { HttpClientModule } from '@angular/common/http';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { NgbDatepickerI18n } from '@ng-bootstrap/ng-bootstrap';
import { I18n, CustomDatepickerI18n } from './datepicker-i18n';
import { TinymceModule } from 'angular2-tinymce';

import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { CalendarModule, DateAdapter } from 'angular-calendar';
import { adapterFactory } from 'angular-calendar/date-adapters/date-fns';

import { DatePipe,DecimalPipe } from '@angular/common';
import { HtmlSpecialDecodePipe, FormatNewLineHtml, FormatManyDatesView, FormatPhone, Negative, ParseDate } from '../pipes/customPipes';

import { AppComponent } from './app.component';
import { AppRoutingModule } from './app.routing';

import { CalendarComponent } from '../components/calendar/calendar';
import { CalendarWeekComponent } from '../components/calendarWeek/calendarWeek';
import { ImageSelector } from '../components/imageSelector/imageSelector';
import { BookingEditorComponent } from '../components/bookingEditor/bookingEditor';
import { BookingComponent } from '../components/booking/booking';
import { BookingDialogComponent } from '../components/bookingDialog/bookingDialog';
import { ConstraintComponent } from '../components/constraint/constraint';
import { InfoCalendarComponent } from '../components/infoCalendar/infoCalendar';
import { NotificationComponent } from '../components/notification/notification';
import { ReceiptComponent } from '../components/receipt/receipt';
import { LogNotificationsCenterComponent } from '../components/logNotificationsCenter/logNotificationsCenter';
import { RESANewsComponent } from '../components/resaNews/resaNews';
import { SubMenuComponent } from '../components/subMenu/subMenu';
import { SearchBarComponent } from '../components/searchBar/searchBar';
import { TutoButtonComponent } from '../components/tutoButton/tutoButton';

import { CanDeactivateGuard } from '../others/CanDeactivateGuard';
import { rewriteURLs } from '../others/TinyMCECustom';

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

//locale fix fr
import { registerLocaleData } from '@angular/common';
import localeFr from '@angular/common/locales/fr';
registerLocaleData(localeFr, 'fr');
import { HashLocationStrategy, LocationStrategy } from '@angular/common';

import '../assets/tinymce/plugins/RESAShortcodes/plugin.js';
import {APP_BASE_HREF} from '@angular/common';

@NgModule({
  declarations: [
    AppComponent,
    ActivitiesComponent,
    ActivityComponent,
    FormsComponent,
    MembersComponent,
    ReductionsComponent,
    EquipmentsComponent,
    GroupsComponent,
    NotificationsComponent,
    SettingsComponent,
    CalendarsComponent,
    CalendarComponent,
    CalendarWeekComponent,
    ImageSelector,
    BookingEditorComponent,
    BookingComponent,
    BookingDialogComponent,
    NotificationComponent,
    ConstraintComponent,
    InfoCalendarComponent,
    ReceiptComponent,
	  PlanningComponent,
    PlanningMembersComponent,
    PlanningGroupsComponent,
    BookingsListComponent,
    QuotationsListComponent,
    BookingsCalendarComponent,
    BookingsDetailsComponent,
    CustomersComponent,
    CustomerComponent,
    LogNotificationsCenterComponent,
    RESANewsComponent,
    SubMenuComponent,
    SearchBarComponent,
    TutoButtonComponent,
    DailyComponent,
    VouchersComponent,
    MembersStatsComponent,
    StatisticsActivitiesComponent,
    StatisticsBookingsComponent,
    InstallationComponent,
    HtmlSpecialDecodePipe,
    FormatNewLineHtml,
    FormatManyDatesView,
    FormatPhone,
    Negative,
    ParseDate
  ],
  imports: [
    NgbModule,
    TinymceModule.withConfig({
      skin_url: 'assets/tinymce/skins/lightgray',
      plugins:['link', 'paste', 'table', 'advlist', 'autoresize', 'lists', 'code', 'RESAShortcodes'],
      toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | RESAShortcodes',
      urlconverter_callback: rewriteURLs
    }),
    BrowserAnimationsModule,
    CalendarModule.forRoot({
      provide: DateAdapter,
      useFactory: adapterFactory
    }),
    BrowserModule,
    FormsModule,
    AppRoutingModule,
    HttpModule,
    HttpClientModule
  ],
  providers: [
    CanDeactivateGuard,
    DatePipe,DecimalPipe,HtmlSpecialDecodePipe,ParseDate,
    [{provide: LocationStrategy, useClass: HashLocationStrategy}],
    [I18n, {provide: NgbDatepickerI18n, useClass: CustomDatepickerI18n}]
  ],
  bootstrap: [AppComponent],
  entryComponents: [
    BookingDialogComponent,
    ConstraintComponent,
    InfoCalendarComponent,
    NotificationComponent,
    ReceiptComponent
  ],
})
export class AppModule { }
