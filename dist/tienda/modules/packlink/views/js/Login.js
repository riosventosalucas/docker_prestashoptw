/**
 * 2020 Packlink
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Apache License 2.0
 * that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://www.apache.org/licenses/LICENSE-2.0.txt
 *
 * @author    Packlink <support@packlink.com>
 * @copyright 2020 Packlink Shipping S.L
 * @license   http://www.apache.org/licenses/LICENSE-2.0.txt  Apache License 2.0
 */

var Packlink = window.Packlink || {};

/**
 * Initializes register form on login page.
 */
function initRegisterForm() {
  let registerBtnClicked = function () {
    let form = document.getElementById('pl-register-form');
    let ajaxService = Packlink.ajaxService;
    form.style.display = 'block';

    let closeBtn = document.getElementById('pl-register-form-close-btn');
    closeBtn.addEventListener('click', function () {
      form.style.display = 'none';
    });

    let supportedCountriesUrl = document.getElementById('pl-countries-url').value;

    ajaxService.get(supportedCountriesUrl, populateCountryList);
  };

  /**
   * Populates the list of supported countries on login form.
   */
  let populateCountryList = function (response) {
    let countryList = document.getElementsByClassName('pl-register-country-list-wrapper')[0],
        logoPath =  document.getElementById('pl-logo-path').value;

    if (countryList.childElementCount > 0) {
      return;
    }

    for (let code in response) {
      let supportedCountry = response[code],
          linkElement = document.createElement('a'),
          countryElement = document.createElement('div'),
          imageElement = document.createElement('img'),
          nameElement = document.createElement('div');

      linkElement.href = supportedCountry.registration_link;
      linkElement.target = '_blank';

      countryElement.classList.add('pl-country');

      imageElement.src = logoPath + supportedCountry.code + '.svg';
      imageElement.classList.add('pl-country-logo');
      imageElement.alt = supportedCountry.name;

      countryElement.appendChild(imageElement);

      nameElement.classList.add('pl-country-name');
      nameElement.innerText = supportedCountry.name;

      countryElement.appendChild(nameElement);
      linkElement.appendChild(countryElement);
      countryList.appendChild(linkElement);
    }
  };

  let btn = document.getElementById('pl-register-btn');
  btn.addEventListener('click', registerBtnClicked, true);
}
