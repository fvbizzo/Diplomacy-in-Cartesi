import TerritoryMap, {
  webdipNameToTerritory,
} from "../data/map/variants/classic/TerritoryMap";
import { IProvinceStatus } from "../models/Interfaces";
import { APITerritories } from "../state/interfaces/GameDataResponse";
import TerritoriesMeta from "../state/interfaces/TerritoriesState";

export default function getTerritoriesMeta(data): TerritoriesMeta {
  const {
    territories,
    territoryStatuses,
  }: { territories: APITerritories; territoryStatuses: IProvinceStatus[] } =
    data;

  const territoriesMeta: TerritoriesMeta = {};

  Object.entries(territories).forEach(
    ([
      id,
      { coast, coastParentID, countryID: homeCountryID, name, supply, type },
    ]) => {
      const territory = webdipNameToTerritory[name];
      const territoryStatus = territoryStatuses.find(
        ({ id: territoryID }) => id === territoryID,
      );

      const countryID: string | null =
        homeCountryID === "0" ? null : homeCountryID;
      let ownerCountryID: string | null = null;
      if (territoryStatus) {
        ownerCountryID =
          territoryStatus.ownerCountryID === "0"
            ? null
            : territoryStatus.ownerCountryID;
      }

      if (!territoriesMeta[territory]) {
        territoriesMeta[territory] = {
          coast,
          coastParentID,
          countryID,
          id,
          ownerCountryID,
          standoff: territoryStatus ? territoryStatus.standoff : false,
          supply: supply === "Yes",
          territory,
          type,
          unitID: territoryStatus ? territoryStatus.unitID : null,
          coastChildIDs: [],
        };
      }
    },
  );
  Object.values(territoriesMeta).forEach((meta) => {
    if (meta.coastParentID !== meta.id) {
      const parent = Object.values(territoriesMeta).find(
        (m) => m.id === meta.coastParentID,
      )!.territory;

      territoriesMeta[parent]!.coastChildIDs.push(meta.id);
    }
  });

  return territoriesMeta;
}
