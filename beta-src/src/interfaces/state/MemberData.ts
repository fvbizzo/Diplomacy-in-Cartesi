import IntegerRange from "../../types/IntegerRange";

export interface OrderStatus {
  Completed: boolean;
  None: boolean;
  Ready: boolean;
  Saved: boolean;
  Hidden: boolean;
}

export interface MemberData {
  bet: IntegerRange<5, 96>;
  country: string;
  countryID: number;
  excusedMissedTurns: IntegerRange<0, 5>;
  missedPhases: number;
  newMessagesFrom: number[];
  online: boolean;
  orderStatus: OrderStatus;
  pointsWon?: number | null;
  status: string;
  supplyCenterNo: number;
  timeLoggedIn: number;
  unitNo: number;
  userID: number;
  username: string;
  votes: string[];
}
