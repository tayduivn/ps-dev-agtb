import * as TextField from '../fields/text-field';
import * as TextareaField from '../fields/textarea-field';
import * as NameField from '../fields/name-field';
import * as EnumField from '../fields/enum-field';
import * as IntField from '../fields/int-field';
import * as FloatField from '../fields/float-field';
import * as DateField from '../fields/date-field';
import * as RelateField from '../fields/relate-field';
import * as CopyField from '../fields/copy-field';
import * as CurrencyField from '../fields/currency-field';
import * as UrlField from '../fields/url-field';
import * as FullnameField from '../fields/fullname-field';
import * as TagField from '../fields/tag-field';

export const FIELD_TYPES__MAP = {
    name: NameField,
    phone: TextField,
    fullname: FullnameField,
    url: UrlField,
    text: TextField,
    textarea: TextareaField,
    enum: EnumField,
    tag: TagField,
    int: IntField,
    date: DateField,
    float: FloatField,
    relate: RelateField,
    checkbox: CopyField,
    copy: CopyField,
    currency: CurrencyField,
};

