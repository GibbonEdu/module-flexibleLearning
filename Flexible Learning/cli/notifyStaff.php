<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

use Gibbon\Services\Format;
use Gibbon\Contracts\Comms\Mailer;
use Gibbon\Comms\NotificationSender;
use Gibbon\Domain\Staff\StaffGateway;
use Gibbon\Domain\System\SettingGateway;
use Gibbon\Module\FlexibleLearning\Domain\UnitSubmissionGateway;

$_POST['address'] = '/modules/Flexible Learning/report_workPendingFeedback.php';

require __DIR__.'/../../../gibbon.php';

// Setup some of the globals
getSystemSettings($guid, $connection2);
setCurrentSchoolYear($guid, $connection2);
Format::setupFromSession($container->get('session'));

if (!isCommandLineInterface()) {
    echo __('This script cannot be run from a browser, only via CLI.');
    return;
}

if (isSchoolOpen($guid, date('Y-m-d'), $connection2, true) == false) { 
    echo __('School is not open, so no emails will be sent.');
    return;
}

if ($_SESSION[$guid]['organisationEmail'] == '') {
    echo __('This script cannot be run, as no school email address has been set.');
    return;
}

$expectFeedback =  $container->get(SettingGateway::class)->getSettingByScope('Flexible Learning', 'expectFeedback');
if ($expectFeedback != 'Y') {
    echo __m('Feedback is not expected at this time, the CLI will not run.');
    return;
}

// Override the ini to keep this process alive
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 1800);
set_time_limit(1800);

// Get the recent submissions
$unitSubmissionGateway = $container->get(UnitSubmissionGateway::class);
$criteria = $unitSubmissionGateway->newQueryCriteria()
    ->sortBy('timestampSubmitted')
    ->filterBy('status', 'Pending');

$submissions = $unitSubmissionGateway->queryPendingFeedback($criteria, $session->get('gibbonSchoolYearID'))->toArray();

if (empty($submissions)) {
    echo __('No recent submissions.');
    return;
}

// Get the list of teachers
$staffGateway = $container->get(StaffGateway::class);
$criteria = $staffGateway->newQueryCriteria()
    ->sortBy('surname')
    ->filterBy('status', 'Full')
    ->filterBy('type', 'Teaching');

$staff = $staffGateway->queryAllStaff($criteria)->toArray();

// Send email
$mail = $container->get(Mailer::class);

$subject = __m('Flexible Learning: {count} students awaiting feedback', ['count' => count($submissions)]);
$body = __m('There are currently {count} students awaiting feedback on their Flexible Learning. Click below to view the list of work pending feedback.', ['count' => count($submissions)]);

foreach ($staff as $person) {
    $mail->AddBcc($person['email'], Format::name('', $person['preferredName'], $person['surname'], 'Staff', false, true));
}

$mail->setDefaultSender($subject);
$mail->renderBody('mail/message.twig.html', [
    'title'  => __m('Work Pending Feedback'),
    'body'   => $body,
    'button' => [
        'url'  => "index.php?q=/modules/Flexible Learning/report_workPendingFeedback.php",
        'text' => __('View Details'),
    ],
]);

$sent = $mail->Send();


// Send a notification to the admin
$actionText = __m('A Flexible Learning CLI script has run.').'<br/><br/>';
$actionText .= $sent
    ? __m('A notification was sent to {staffCount} teachers letting them know about {count} students awaiting feedback for their Flexible Learning.', ['staffCount' => count($staff), 'count' => count($submissions)])
    : __m('A notification failed to send to {staffCount} teachers.', ['staffCount' => count($staff)]);
$actionLink = '/index.php?q=/modules/Flexible Learning/report_workPendingFeedback.php';

$notificationSender = $container->get(NotificationSender::class);
$notificationSender->addNotification($session->get('organisationAdministrator'), $actionText, 'Flexible Learning', $actionLink);
$notificationSender->sendNotifications();


// Output the result to terminal
echo sprintf('Sent %1$s emails.', count($staff))."\n";
