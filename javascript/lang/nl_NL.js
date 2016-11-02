if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
    //console.error('Class ss.i18n not defined');
} else {
    ss.i18n.addDictionary('nl_NL', {
        'All.LOGINWRONGDATA':                       'The credentials are not correct',
        'Form.FIELD_MUST_BE_EMPTY':                 'Dit veld mag leeg zijn.',
        'Form.FIELD_MAY_NOT_BE_EMPTY':              'Dit veld mag niet leeg zijn.',
        'Form.FIELD_MUST_BE_FILLED_IN':             'Please fill in this field.',
        'Form.MIN_CHARS':                           'Enter at least %s characters.',
        'Form.FIELD_REQUIRES_NR_OF_CHARS':          'This field requires exactly %s characters.',
        'Form.REQUIRES_SAME_VALUE_AS_IN_FIELD':     'Please enter the same value as in field "%s".',
        'Form.REQUIRES_OTHER_VALUE_AS_IN_FIELD':    'This field may not have the same value as field "%s".',
        'Form.MAX_DECIMAL_PLACES_ALLOWED':          'This field can not have more than %s decimal places.',
        'Form.NUMBERS_ONLY':                        'This field may consist of numbers only.',
        'Form.QUANTITY_ONLY':                       'This field may consist of numbers and "." or "," only.',
        'Form.CURRENCY_ONLY':                       'Please enter a valid currency amount (e.g. 1499,00).',
        'Form.DATE_ONLY':                           'Please enter a valid  date in the format ("dd.mm.yyyy").',
        'Form.HASNOSPECIALSIGNS':                   'This field must contain special characters (other characters than letters, numbers and "@" and ".").',
        'Form.HASSPECIALSIGNS':                     'This field must not contain special characters (only letters, numbers and the "@" and ".").',
        'Form.MUSTNOTBEEMAILADDRESS':               'Please don\'t enter an email address here.',
        'Form.MUSTBEEMAILADDRESS':                  'Please enter a valid email address.'
    });
}
