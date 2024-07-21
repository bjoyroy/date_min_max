# Date Min Max

[![DOI](http://zenodo.org/badge/DOI/10.5281/zenodo.4681708.svg)](http://doi.org/10.5281/zenodo.4681708)

This REDCap module adds actions tags __@DATE-MIN__ and __@DATE-MAX__ that can be applied to date fields - i.e., a text field with date validation applied.


## Prerequisites
- REDCap >= 12.0.0

## Installation
- Clone this repo into to `<redcap-root>/modules/date_min_max_v1.0.0`.
- Go to **Control Center > Manage External Modules** and enable _Date Min Max_.
- For each project you want to use this module, go to the project home page, click on **Manage External Modules** link, and then enable _Date Min Max_ for that project.

## Expected Behavior

### Scenarios



### Existing Data
The action tags will _only_ apply to empty fields; if data already exists in a field when the form is loaded the action tag will _not_ apply to that field. This is to prevent annoyances when revisiting forms.

### Date vs Datetime field

