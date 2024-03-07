import Country from "../enums/Country";
import { MemberData } from "./state/MemberData";

export interface CountryTableData extends MemberData {
  abbr: string;
  supplyCenterNo: number;
  color: string;
  power: Country;
  unitNo: number;
  votes: string[];
}
