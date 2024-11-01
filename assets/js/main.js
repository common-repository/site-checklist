var voClickedButton,
    voList,
    voSidebar,
    voContainer,
    voScreenshotsContainer,
    voLoadingIcon,
    countSuccesses = 0,
    countFails = 0,
    countInfo = 0,
    countNotices = 0;

jQuery(document).ready(function ($) {
    voContainer = $('.vocheck-container');
    voScreenshotsContainer = $('.vocheck-screenshots-container');
    voList = $('.vocheck-list');
    voSidebar = $('.vocheck-sidebar');
    voLoadingIcon = $('.vocheck-loading');

    $(".vocheck-button.vocheck-checks").on('click', function () {
        voClickedButton = $(this);
        beforeRunningChecks();
        startChecklistProcess();
    });

    $(".vocheck-button.vocheck-screenshots").on('click', function () {
        voClickedButton = $(this);
        beforeRunningChecks();
        sendAjaxCheck('get_screenshots_of_website');
    });

    // Tabs
    $('.inline-list-links').each(function() {
        $(this).find('li').each(function(i) {
            $(this).click(function(){
                $(this).addClass('current').siblings().removeClass('current')
                    .parents('#wpbody').find('div.panel').hide().end().find('div.panel:eq('+i+')').fadeIn(150);
                return false;
            });
        });
    });

    // Little easter egg
    var count = 0;
    $('.vocheck-loading').click(function() {
       count++;
       if (count > 12) {
           $('#wpcontent').css('background-color', 'greenyellow');
           $(this).css('width', '300%');
           $(this).css('height', '300%');
           $(this).css('position', 'absolute');
           $(this).css('left', '0');
           $(this).css('top', '0');
       }
    });
});

function beforeRunningChecks() {
    voList.empty();
    voScreenshotsContainer.empty();
    voSidebar.empty();
    jQuery('.vocheck-button').prop('disabled', true);
    voLoadingIcon.addClass('vocheck-spinning');

    jQuery('.vocheck-overview-count-container').hide();
    resetResultCounter();


    //Trigger afterRunningChecks() after all checks are done
    jQuery(document).ajaxStop(function () {
        afterRunningChecks();
    });
}

/**
 * Retrieve array with the current checks
 */
function startChecklistProcess() {
    jQuery.post(params.ajaxurl, {action: 'return_checks'})
    .done(function (data) {
        var checks = JSON.parse(data);
        startChecks(checks);
    });
}

/**
 * Trigger checks
 */
function startChecks(checks) {
    jQuery.each(checks, function(index, value) {
        sendAjaxCheck(value);
    });
}

/**
 * Action after checks completed
 */
function afterRunningChecks() {
    jQuery('.vocheck-button').prop('disabled', false);
    voLoadingIcon.removeClass('vocheck-spinning');

    jQuery('.vocheck-overview-count-container').show();
    jQuery('.vocheck-overview-count--success').text(countSuccesses);
    jQuery('.vocheck-overview-count--failed').text(countFails);
    jQuery('.vocheck-overview-count--notice').text(countNotices);
    jQuery('.vocheck-overview-count--info').text(countInfo);
    addResultCountFilter('success');
    addResultCountFilter('failed');
    addResultCountFilter('notice');
    addResultCountFilter('info');
}

/**
 * Send an ajax post with appropriate action
 * @param action
 */
function sendAjaxCheck(action) {
    jQuery.post(params.ajaxurl, {action: action})

    .done(function(data) {
        processAjaxResponse(data)
    })
    .fail(function() {
        console.log('Check \'' + action + '\' did not succeed');
    })
}

/**
 * Retrieve ajax response and add listitem
 * @param response
 */
function processAjaxResponse(response) {
    response = JSON.parse(response);

    if (response.debug.length > 0) {
        console.log(response.debug);
    }

    if (response.location == 'list') {

        switch(response.status) {
            case 'success':
                countSuccesses++;
                break;
            case 'notice':
                countNotices++;
                break;
            case 'info':
                countInfo++;
                break;
            default:
                countFails++;
        }

        if (response.status == 'success' || response.status == 'info') {
            appendToList(response.status, response.messages['successMessage']);
        } else {
            if (response.messages['fixMessage'] == "") {
                appendToList(response.status, response.messages['failedMessage']);
            } else {
                appendToList(response.status, response.messages['failedMessage'] + "</span><span class='fix'>" + response.messages['fixMessage'] + "</span>");
            }
        }
    } else if (response.location == 'sidebar') {
        if (response.status == 'success') {
            appendToSidebar(response.status, response.messages['successMessage']);
        } else {
            appendToSidebar(response.status, response.messages['failedMessage'] + "</span>");
        }
    } else if (response.location == 'screenshots') {
        if (response.status == 'success') {
            appendAsScreenshots(response.status, response.messages['successMessage']);
        } else {
            if (response.messages['fixMessage'] == "") {
                appendAsScreenshots(response.status, response.messages['failedMessage']);
            } else {
                appendAsScreenshots(response.status, response.messages['failedMessage'] + "</span><span class='fix'>" + response.messages['fixMessage'] + "</span>");
            }
        }
    }
}

/**
 * Append listitem to voContainer
 * @param status success|failed|notice
 * @param content Message for user
 */
function appendToList(status, content) {
    switch (status) {
        case 'success':
            voList.append('<li class="vocheck-list-item vocheck-list-item--success"><span>' + content + '</span></li>');
            break;
        case 'failed':
            voList.append('<li class="vocheck-list-item vocheck-list-item--failed"><span>' + content + '</span></li>');
            break;
        case 'notice':
            voList.append('<li class="vocheck-list-item vocheck-list-item--notice"><span>' + content + '</span></li>');
            break;
        case 'info':
            voList.append('<li class="vocheck-list-item vocheck-list-item--info"><span>' + content + '</span></li>');
            break;
    }
}

/**
 * Append item to voSidebar
 * @param status success|failed
 * @param content Message for user
 */
function appendToSidebar(status, content) {
    switch (status) {
        case 'success':
            voSidebar.append('<div class="vocheck-sidebar-item vocheck-sidebar-item--success">' + content + '</div>');
            break;
        case 'failed':
            voSidebar.append('<div class="vocheck-sidebar-item vocheck-sidebar-item--failed">' + content + '</div>');
            break;
        case 'notice':
            voSidebar.append('<div class="vocheck-sidebar-item vocheck-sidebar-item--notice">' + content + '</div>');
            break;
    }
}

/**
 * Append listitem to voContainer
 * @param status success|failed|notice
 * @param content Message for user
 */
function appendAsScreenshots(status, content) {
    switch (status) {
        case 'success':
            voScreenshotsContainer.append('<li class="vocheck-list-item"><span>' + content + '</span></li>');
            break;
        case 'failed':
            voScreenshotsContainer.append('<li class="vocheck-list-item"><span>' + content + '</span></li>');
            break;
        case 'notice':
            voScreenshotsContainer.append('<li class="vocheck-list-item"><span>' + content + '</span></li>');
            break;
    }
}

/**
 * Add filters
 * @param status
 */
function addResultCountFilter(status) {
    jQuery('.vocheck-overview-count--' + status).on('click', function() {
        jQuery('.vocheck-list-item--' + status).toggle();
    });
}

/**
 * Reset counters
 */
function resetResultCounter() {
    countFails = 0;
    countSuccesses = 0;
    countInfo = 0;
    countNotices = 0;
}