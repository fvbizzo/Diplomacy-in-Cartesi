import ConvoyGroupClass from "./ConvoyGroupClass";
import { IUnit } from "./Interfaces";
import TerritoryClass from "./TerritoryClass";

export default class UnitClass {
  id!: string;

  countryID!: string;

  convoyLink!: boolean;

  type!: string;

  terrID!: string;

  Territory!: TerritoryClass;

  ConvoyGroup!: ConvoyGroupClass;

  constructor(data: IUnit) {
    Object.assign(this, { ...data, convoyLink: false });
  }

  setTerritory(territory: TerritoryClass) {
    this.Territory = territory;
  }

  setConvoyGroup(convoyGroup: ConvoyGroupClass) {
    this.ConvoyGroup = convoyGroup;
  }

  setConvoyLink() {
    this.convoyLink = true;
  }
}
